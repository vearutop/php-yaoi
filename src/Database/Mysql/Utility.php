<?php

namespace Yaoi\Database\Mysql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Sql\Symbol;
use Yaoi\String\Lexer;

class Utility extends \Yaoi\Database\Utility
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



    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        $statement = $this->database->query("SHOW CREATE TABLE ?", new Symbol($tableName))->fetchRow('Create Table');
        $createTableReader = new CreateTableReader($statement, $this->database);
        return $createTableReader->getDefinition();

        /*
        $schemaReader = new SchemaReader($this->database);
        $definition = $schemaReader->getTableDefinition($tableName);
        return $definition;
        */
    }


    public function getColumnTypeString(Column $column)
    {
        $typeString = new TypeString($this->database);
        return $typeString->getByColumn($column);
    }

    /**
     * @inheritdoc
     */
    public function checkTable(Table $table)
    {
        foreach ($table->getColumns(true) as $column) {
            if ($column->flags & Column::TIMESTAMP) {
                if (!$column->getDefault()) {
                    $column->setDefault('0000-00-00 00:00:00');
                    $column->setFlag(Column::NOT_NULL);
                }
            }
        }
    }


    public function generateCreateTableOnDefinition(Table $table) {
        $expression = new CreateTable($table);
        return $expression;
    }

    public function generateAlterTable(Table $before, Table $after)
    {
        return new AlterTable($before, $after);
    }

    public function tableExists($tableName)
    {
        $rows = $this->database->query("SHOW TABLES LIKE ?", $tableName)->fetchAll();
        return (bool)$rows;
    }


    /** @var  Lexer\Parser */
    private $tokenizer;
    public function getStatementTokenizer() {
        if (null === $this->tokenizer) {
            $this->tokenizer = $tokenizer = new Lexer\Parser();

            $tokenizer
                ->addQuote('`', '`', array('``' => '`'))
                ->addQuote(
                    "'", "'", array(
                        '\\\'' => '\'',
                        '\"' => '"',
                        '\r' => "\r",
                        '\n' => "\n",
                    )
                )
                ->addQuote(
                    '"', '"', array(
                        '\\\'' => '\'',
                        '\"' => '"',
                        '\r' => "\r",
                        '\n' => "\n",
                    )
                )
                ->addLineStopper('#')
                ->addLineStopper('-- ')
                ->addBracket('(', ')')
                ->addDelimiter(',')
                ;
        }
        return $this->tokenizer;
    }


}