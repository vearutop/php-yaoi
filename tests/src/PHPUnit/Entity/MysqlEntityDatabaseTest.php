<?php

namespace YaoiTests\PHPUnit\Entity;

use Yaoi\Database;
use YaoiTests\Helper\Entity\TestEntityDB;
use YaoiTests\PHPUnit\Entity\TestEntityDatabaseUnified;

class MysqlEntityDatabaseTest extends TestEntityDatabaseUnified
{

    public function setUp()
    {
        //$this->markTestSkipped('Test is deprecated');

        try {
            $db = Database::getInstance('test_mysqli');
        } catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        $createSQL = <<<SQL
CREATE TABLE test_entity_db (
`id` integer unsigned auto_increment,
`name` varchar(255),
`age` tinyint,
`weight` tinyint,
`url` varchar(255),
`birth_date` timestamp,
PRIMARY KEY(`id`))
SQL;

        $db->query("DROP TABLE IF EXISTS `test_entity_db`");
        $db->query($createSQL);

        TestEntityDB::bindDatabase($db);
    }
}