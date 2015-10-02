<?php
namespace YaoiTests\PHPUnit\Database;

use Yaoi\Database;
use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\App;

class BindsTest extends TestCase
{
    public function setUp()
    {
        \YaoiTests\Helper\Database\CheckAvailable::getMysqli();
    }

    public function testUnnamedBinds()
    {
        $db = App::database('test_mysqli')->mock();

        $expected = 'SELECT 1, \'two\', NULL, 0.445453';
        $this->assertSame($expected, $db->query("SELECT ?, ?, ?, ?", 1, 'two', null, 0.445453)->skipAutoExecute()->build());
        $this->assertSame($expected, $db->query("SELECT ?, ?, ?, ?", array(1, 'two', null, 0.445453))->skipAutoExecute()->build());
        $this->assertSame($expected, $db->query("SELECT :one, :two, :three, :four",
            array('one' => 1, 'two' => 'two', 'three' => null, 'four' => 0.445453))->skipAutoExecute()->build());


        $expected = 'SELECT 1, 1, 1';
        $this->assertSame($expected, $db->query("SELECT :one, :one, :one",
            array('one' => 1, 'two' => 'two'))->skipAutoExecute()->build());

        $this->assertSame($expected, $db->query("SELECT :one, :one, :one",
            array('one' => 1))->skipAutoExecute()->build());


    }


    public function testDestruct()
    {
        return;
        $db = Database::create(Database::$instanceConfig['test_mysqli'])->query("SHOW TABLES");
        unset($db);


    }


}