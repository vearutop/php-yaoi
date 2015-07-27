<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Definition\Index;
use Yaoi\Database\Exception;
use Yaoi\Sql\Symbol;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

class Mysql extends Utility
{
    public function killSleepers($timeout = 30)
    {
        foreach ($this->database->query("SHOW PROCESSLIST") as $row) {
            if ($row['Time'] > $timeout) {
                $this->database->query("KILL $row[Id]");
            }
        }
        return $this;
    }


    const _PRIMARY = 'PRIMARY';

    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        $tableSymbol = new Symbol($tableName);
        $res = $this->database->query("DESC ?", $tableSymbol);
        $columns = new \stdClass();
        while ($row = $res->fetchRow()) {
            $type = $row['Type'];
            $field = $row['Field'];

            $phpType = $this->getTypeByString($type);

            if ('auto_increment' === $row['Extra']) {
                $phpType += Column::AUTO_ID;
            }

            $column = new Column($phpType);
            $columns->$field = $column;
            $column->schemaName = $field;
            $column->setDefault($row['Default']);
            $column->setFlag(Column::NOT_NULL, $row['Null'] === 'NO');
        }

        $definition = new Table($columns);

        $res = $this->database->query("SHOW INDEX FROM ?", $tableSymbol);
        $indexes = array();
        $uniqueIndex = array();
        foreach ($res as $row) {
            $indexes [$row['Key_name']][$row['Seq_in_index']] = $columns->{$row['Column_name']};
            $uniqueIndex [$row['Key_name']] = !$row['Non_unique'];
        }

        foreach ($indexes as $indexName => $indexData) {
            ksort($indexData);
            $index = new Index(array_values($indexData));
            $index->setName($indexName);
            $index->setType($uniqueIndex[$indexName] ? Index::TYPE_UNIQUE : Index::TYPE_KEY);
            if ($indexName === self::_PRIMARY) {
                $definition->setPrimaryKey($index->columns);
            }
            else {
                $definition->addIndex($index);
            }
        }

        return $definition;
    }

    public function generateCreateTableOnDefinition(Table $table) {
        $statement = 'CREATE TABLE `' . $table->schemaName . '` (' . PHP_EOL;

        foreach ($table->getColumns(true) as $column) {
            $statement .= ' `' . $column->schemaName . '` ' . $this->getColumnTypeString($column);

            if ($column->flags & Column::AUTO_ID) {
                $statement .= ' AUTO_INCREMENT';
            }

            $statement .= ',' . PHP_EOL;
        }

        foreach ($table->indexes as $index) {
            $indexString = '';
            foreach ($index->columns as $column) {
                $indexString .= '`' . $column->schemaName . '`, ';
            }
            $indexString = substr($indexString, 0, -2);

            if ($index->type === Index::TYPE_KEY) {
                $statement .= ' KEY (' . $indexString . '),' . PHP_EOL;
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $statement .= ' UNIQUE KEY (' . $indexString . '),' . PHP_EOL;
            }
        }

        foreach ($table->constraints as $constraint) {
            /** @var Column $fk */
            $fk = $constraint[0];
            /** @var Column $ref */
            $ref = $constraint[1];
            $constraintName = $table->schemaName . '_' . $fk->schemaName;

            $statement .= ' CONSTRAINT `' . $constraintName . '` FOREIGN KEY (`' . $fk->schemaName . '`) REFERENCES `'
                . $ref->table->schemaName . '` (`' . $ref->schemaName . '`),' . PHP_EOL;
        }

        $statement .= ' PRIMARY KEY (';
        foreach ($table->primaryKey as $column) {
            $statement .= '`' . $column->schemaName . '`,';
        }
        $statement = substr($statement, 0, -1);
        $statement .= ')' . PHP_EOL;

        $statement .= ')' . PHP_EOL;

        return $statement;
    }


    private function getTypeByString($type) {
        $phpType = Column::STRING;
        switch (true) {
            case 'bigint' === substr($type, 0, 6):
            case 'int' === substr($type, 0, 3):
            case 'mediumint' === substr($type, 0, 9):
            case 'smallint' === substr($type, 0, 8):
            case 'tinyint' === substr($type, 0, 7):
                $phpType = Column::INTEGER;
                break;

            case 'decimal' === substr($type, 0, 7):
            case 'double' === $type:
            case 'float' === $type:
                $phpType = Column::FLOAT;
                break;

            case 'date' === $type:
            case 'datetime' === $type:
            case 'timestamp' === $type:
                $phpType = Column::TIMESTAMP;
                break;

        }
        return $phpType;
    }

    private function getIntTypeString(Column $column) {
        $intType = 'int';

        // TODO implement SIZE_ definitions
        /*
        switch (true) {
            case $flags & Column::SIZE_1B:
                $intType = 'tinyint';
                break;

            case $flags & Column::SIZE_2B:
                $intType = 'mediumint';
                break;

            case $flags & Column::SIZE_3B:
                $intType = 'mediumint';
                break;


        }
        */
        return $intType;
    }

    private function getFloatTypeString(Column $column) {
        // TODO implement double
        return 'float';
    }

    private function getStringTypeString(Column $column) {
        // TODO implement long strings

        $length = $column->stringLength ? $column->stringLength : 255;
        if ($column->stringFixed) {
            return 'char(' . $length . ')';
        }
        else {
            return 'varchar(' . $length . ')';
        }
    }

    private function getTimestampTypeString(Column $column) {
        if (false === $column->default) {
            $column->default = '0';
        }
        return 'timestamp';
    }

    public function getColumnTypeString(Column $column) {
        $flags = $column->flags;
        switch (true) {
            case ($flags & Column::INTEGER):
                $typeString = $this->getIntTypeString($column);
                break;

            case $flags & Column::FLOAT:
                $typeString = $this->getFloatTypeString($column);
                break;

            case $flags & Column::STRING:
                $typeString = $this->getStringTypeString($column);
                break;

            case $flags & Column::TIMESTAMP:
                $typeString = $this->getTimestampTypeString($column);
                break;

            default:
                throw new Exception('Undefined column type for column ' . $column->propertyName, Exception::INVALID_SCHEMA);

        }

        if ($flags & Column::UNSIGNED) {
            $typeString .= ' unsigned';
        }

        if ($flags & Column::NOT_NULL) {
            $typeString .= ' NOT NULL';
        }

        if (false !== $column->default) {
            $typeString .= $this->database->expr(" DEFAULT ?", $column->default);
        }

        return $typeString;
    }

    public function dropTableIfExists($tableName)
    {
        $this->database->query("DROP TABLE IF EXISTS ?", new Symbol($tableName));
    }

    public function dropTable($tableName)
    {
        $this->database->query("DROP TABLE ?", new Symbol($tableName));
    }

    public function generateAlterTable(Table $before, Table $after)
    {
        $alter = array();

        $beforeColumns = $before->getColumns(true);
        foreach ($after->getColumns(true) as $columnName => $afterColumn) {
            $afterTypeString = $this->getColumnTypeString($afterColumn);

            if (!isset($beforeColumns[$columnName])) {
                $alter []= 'ADD COLUMN `' . $afterColumn->schemaName . '` ' . $afterTypeString;
            }
            else {
                $beforeColumn = $beforeColumns[$columnName];
                if ($this->getColumnTypeString($beforeColumn) !== $afterTypeString) {
                    $alter []= 'MODIFY COLUMN `' . $afterColumn->schemaName . '` ' . $afterTypeString;
                }
                unset($beforeColumns[$columnName]);
            }
        }
        foreach ($beforeColumns as $columnName => $beforeColumn) {
            $alter []= 'DROP COLUMN `' . $beforeColumn->schemaName . '`';
        }

        $beforeIndexes = $before->indexes;
        foreach ($after->indexes as $indexId => $index) {
            if (!isset($beforeIndexes[$indexId])) {
                $alter []= 'ADD '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX `' . $index->getName() . '` ()';
            }
            else {
                unset($beforeIndexes[$indexId]);
            }
        }
        foreach ($beforeIndexes as $indexId => $index) {
            $alter []= 'DROP INDEX `' . $index->getName() . '`';
        }

        return $alter;
    }


}