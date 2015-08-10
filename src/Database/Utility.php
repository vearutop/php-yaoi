<?php

namespace Yaoi\Database;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Utility\Contract as UtilityContract;
use Yaoi\BaseClass;
use Yaoi\Database\Contract as DatabaseContract;
use Yaoi\Sql\AlterTable;
use Yaoi\Sql\CreateTable;
use Yaoi\Sql\Symbol;

abstract class Utility extends BaseClass implements UtilityContract
{
    /**
     * @var DatabaseContract
     */
    protected $database;

    /**
     * @param Contract $database
     * @return $this
     */
    public function setDatabase(DatabaseContract $database)
    {
        $this->database = $database;
        return $this;
    }


    /**
     * Check/fix database related type misconceptions
     *
     * @param Table $table
     */
    abstract public function checkTable(Table $table);


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
        $expression = new AlterTable();
        $expression->bindDatabase($this->database)->generate($before, $after);
        return $expression->build();
    }


}
