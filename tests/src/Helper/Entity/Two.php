<?php

namespace YaoiTests\Helper\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;
use YaoiTests\Helper\Entity\OneABBR;

class Two extends Entity
{
    public $id;
    public $oneId;
    public $createdAt;
    public $updatedAt;
    public $info;

    /**
     * Setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->oneId = OneABBR::columns()->id;
        $columns->createdAt = Column::TIMESTAMP;
        $columns->updatedAt = Column::TIMESTAMP;
        $columns->info = Column::create(Column::STRING)->setIndexed();
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setSchemaName('custom_name');
    }
}