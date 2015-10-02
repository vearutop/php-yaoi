<?php

namespace YaoiTests\PHPUnit\Sql;


use Yaoi\Database;

class TestBase extends \Yaoi\Test\PHPUnit\TestCase
{
    protected $db;

    public function setUp()
    {
        try {
            $this->db = Database::getInstance('test_mysqli');
        } catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }


}