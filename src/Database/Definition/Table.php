<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Log;
use Yaoi\Sql\CreateTable;
use Yaoi\String\Utils;

class Table extends BaseClass
{
    /** @var Column */
    public $autoIdColumn;

    /** @var Column[] */
    public $primaryKey = array();

    /** @var Index[]  */
    public $indexes = array();

    /** @var \stdClass  */
    public $columns;


    public $schemaName; // tODO exception on empty schemaName

    public function setSchemaName($schemaName) {
        $this->schemaName = $schemaName;
        return $this;
    }

    public $className;

    public function __construct(\stdClass $columns = null, Database\Contract $database = null, $schemaName) {
        $this->schemaName = $schemaName;
        $this->database = $database;
        if (null !== $columns) {
            $this->setColumns($columns);
        }
    }

    /**
     * @param bool $asArray
     * @param bool $bySchemaName
     * @return array|Column[]|\stdClass
     */
    public function getColumns($asArray = false, $bySchemaName = false) {
        if ($bySchemaName) {
            $columns = array();
            /** @var Column $column */
            foreach ((array)$this->columns as $column) {
                $columns [$column->schemaName]= $column;
            }
            return $asArray ? $columns : (object)$columns;
        }

        return $asArray ? (array)$this->columns : $this->columns;
    }

    /**
     * @param $name
     * @return null|Column
     */
    public function getColumn($name) {
        return isset($this->columns->$name) ? $this->columns->$name : null;
    }


    private $columnForeignKeys = array();

    /**
     * @param Column $column
     * @return null|ForeignKey
     */
    public function getForeignKeyByColumn(Column $column) {
        $name = $column->propertyName;
        if (isset($this->columnForeignKeys[$name])) {
            return $this->columnForeignKeys[$name];
        }
        else {
            return null;
        }
    }

    private function setColumns($columns) {
        if (is_object($columns)) {
            $this->columns = $columns;
        }

        /**
         * @var string $name
         * @var Column $column
         */
        foreach ((array)$this->columns as $name => $column) {
            if (is_int($column)) {
                $column = new Column($column);
                $this->columns->$name = $column;
            }

            // another column reference
            if (!empty($column->table) && $column->table->schemaName != $this->schemaName) {
                $refColumn = $column;
                $column = clone $column;

                $column->propertyName = $name;
                $column->schemaName = Utils::fromCamelCase($name);
                $column->table = $this;

                $this->columns->$name = $column;
                $foreignKey = new ForeignKey(array($column), array($refColumn));
                $this->columnForeignKeys [$name]= $foreignKey;
                $this->addForeignKey($foreignKey);
                $column->setFlag(Column::AUTO_ID, false);
            }
            else {
                $column->propertyName = $name;
                $column->schemaName = Utils::fromCamelCase($name);
                $column->table = $this;
            }


            if ($column->flags & Column::AUTO_ID) {
                $this->autoIdColumn = $column;
                if (!$this->primaryKey) {
                    $this->primaryKey = array($column->schemaName => $column);
                }
            }

            if ($column->isUnique) {
                $index = new Index($column);
                $index->setType(Index::TYPE_UNIQUE);
                $this->addIndex($index);
            }
            elseif ($column->isIndexed) {
                $index = new Index($column);
                $index->setType(Index::TYPE_KEY);
                $this->addIndex($index);
            }
        }

        $this->database->getUtility()->checkTable($this);

        return $this;
    }

    /**
     * @param Column[]|Column $columns
     * @return $this
     */
    public function setPrimaryKey($columns) {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        $this->primaryKey = array();
        /** @var Column $column */
        foreach ($columns as $column) {
            $this->primaryKey [$column->schemaName]= $column;
        }
        return $this;
    }


    public function addIndex($index) {
        if (!$index instanceof Index) {
            $args = func_get_args();
            $type = array_shift($args);
            $columns = $args;

            $index = Index::create($columns)->setType($type);
        }

        if ($index->type === Index::TYPE_PRIMARY) {
            $this->setPrimaryKey($index->columns);
            return $this;
        }

        $this->indexes []= $index;

        return $this;
    }

    /** @var array|Table[]  */
    public $dependentTables = array();
    /**
     * @var ForeignKey[]
     */
    public $foreignKeys = array();
    public function addForeignKey(ForeignKey $foreignKey) {
        $foreignKey->getReferencedTable()->dependentTables [$this->schemaName]= $this;
        $this->foreignKeys []= $foreignKey;
        return $this;
    }


    private $database;

    /**
     * @return Database\Contract
     * @throws Database\Exception
     */
    public function database()
    {
        return $this->database;
    }


    /**
     * @return CreateTable
     * @throws Database\Exception
     */
    public function getCreateTable() {
        return $this->database()->getUtility()->generateCreateTableOnDefinition($this);
    }


    public function getAlterTableFrom(Table $before) {
        return $this->database()->getUtility()->generateAlterTable($before, $this);
    }


    public function migration() {
        return new Database\Entity\Migration($this);
    }


    public $disableForeignKeys = false;
    public function disableDatabaseForeignKeys($disable = true) {
        $this->disableForeignKeys = $disable;
        return $this;
    }

}