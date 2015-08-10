<?php

use Yaoi\Database;

require_once __DIR__ . '/TestEntityDatabaseUnified.php';

class SqliteEntityDatabaseTest extends TestEntityDatabaseUnified {
    public function setUp() {
        //$this->markTestSkipped('Test is deprecated');

        $dbPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-sqlite5.db';

        /*
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        */
        $db = new Database('sqlite:///' . $dbPath);

        $createSQL = <<<SQL
CREATE TABLE "test_entity_db" (
"id" INTEGER PRIMARY KEY,
"name",
"age",
"weight",
"url",
"birth_date")
SQL;
        $db->query('DROP TABLE IF EXISTS "test_entity_db"');
        $db->query($createSQL);

        TestEntityDb::bindDatabase($db);
    }

}