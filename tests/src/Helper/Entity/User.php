<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 29.09.2015
 * Time: 14:11
 */

namespace YaoiTests\Helper\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\Session;

class User extends Entity
{
    public $id;
    public $name;
    public $age;
    public $hostId;
    public $sessionId;
    public $firstName;
    public $lastName;

    public static $revision = 1;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->name = Column::STRING + Column::NOT_NULL;

        if (self::$revision > 1) {
            $columns->age = Column::INTEGER;
            $columns->hostId = Host::columns()->id;
        }

        if (self::$revision > 2) {
            $columns->sessionId = Session::columns()->id;
            unset($columns->hostId);
            unset($columns->name);
            $columns->firstName = Column::STRING + Column::NOT_NULL;
            $columns->lastName = Column::STRING + Column::NOT_NULL;
        }
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        if (self::$revision > 1) {
            $table->addIndex(Index::TYPE_KEY, $columns->age);

        }

        if (self::$revision > 2) {
            $table->addIndex(Index::TYPE_UNIQUE, $columns->lastName, $columns->firstName);
        }
    }

}