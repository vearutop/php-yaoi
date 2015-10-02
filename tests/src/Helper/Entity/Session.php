<?php
namespace YaoiTests\Helper\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\ForeignKey;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;
use YaoiTests\Helper\Entity\Host;

class Session extends Entity
{
    public $id;
    public $hostId;
    public $startedAt;
    public $endedAt;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->hostId = Host::columns()->id;
        $columns->startedAt = Column::TIMESTAMP;
        $columns->endedAt = Column::TIMESTAMP;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table
            ->setSchemaName('yaoi_tests_entity_session')
            ->getForeignKeyByColumn($columns->hostId)
            ->setOnDelete(ForeignKey::CASCADE)
            ->setOnUpdate(ForeignKey::CASCADE);
    }


}