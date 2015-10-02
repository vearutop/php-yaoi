<?php
namespace YaoiTests\Helper\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;
use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\Database;

/**
 * Class TestEntityDB
 */
class TestEntityDB extends Entity
{
    public $id;
    public $name;
    public $age;
    public $weight;
    public $url;
    public $birthDate;

    /**
     * Setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->name = Column::create()->setStringLength(15, true);
        $columns->age = Column::INTEGER;
        $columns->weight = Column::INTEGER + Column::UNSIGNED;
        $columns->url = Column::STRING;
        $columns->birthDate = Column::TIMESTAMP;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param \Yaoi\Database\Definition\Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setSchemaName('test_entity_db');
    }
}