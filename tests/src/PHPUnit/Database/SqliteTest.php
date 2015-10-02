<?php

namespace YaoiTests\PHPUnit\Database;

use Yaoi\Database;
use Yaoi\Database\Definition\Table;


class SqliteTest extends TestUnified
{
    protected $createTable1 = <<<SQL
CREATE TABLE IF NOT EXISTS test_def (
  id1 INTEGER NOT NULL,
  id2 INTEGER NOT NULL DEFAULT 1,
  name VARCHAR(10) NOT NULL DEFAULT 'Jon Snow',
  address CHAR(10),
  PRIMARY KEY(id1, id2)
);
SQL;


    protected $createTableStatement = <<<SQL
CREATE TABLE test_indexes (
 id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 name varchar(255) NOT NULL,
 uni_one INTEGER DEFAULT NULL,
 uni_two INTEGER DEFAULT NULL,
 default_null float DEFAULT NULL,
 updated timestamp DEFAULT NULL,
 ref_id INTEGER NOT NULL,
 r_one varchar(255) DEFAULT NULL,
 r_two varchar(255) DEFAULT NULL,
 CONSTRAINT fk_test_indexes_ref_id_table_a_id FOREIGN KEY (ref_id) REFERENCES table_a (id),
 CONSTRAINT fk_test_indexes_r_one_r_two_table_a_m_one_table_a_m_two FOREIGN KEY (r_one, r_two) REFERENCES table_a (m_one, m_two)
);
CREATE UNIQUE INDEX unique_uni_one_uni_two ON test_indexes (uni_one, uni_two);
CREATE INDEX key_name ON test_indexes (name);

SQL;

    protected function autoIncrementTest(Table $def)
    {
        // TODO: support autoincrement
    }


    private $dbFileName;

    public function setUp()
    {
        $this->dbFileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testSqlite.sqlite';
        $this->db = new Database('sqlite:///' . $this->dbFileName);
    }

    public function tearDown()
    {
        $this->db->disconnect();
        if (file_exists($this->dbFileName)) {
            unlink($this->dbFileName);
        }
    }

    protected function columnsTest2(Table $def)
    {
        return;
    }


    protected $testCreateIndexesAlterExpected = <<<SQL
ALTER TABLE test_indexes RENAME TO _temp_table;
CREATE TABLE test_indexes (
 id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 name varchar(255) NOT NULL,
 uni_one INTEGER DEFAULT NULL,
 uni_two INTEGER DEFAULT NULL,
 default_null float DEFAULT NULL,
 updated timestamp DEFAULT NULL,
 new_field char(15) NOT NULL DEFAULT 'normal'
);
CREATE UNIQUE INDEX unique_updated ON test_indexes (updated);
INSERT INTO test_indexes (id, name, uni_one, uni_two, default_null, updated) SELECT id, name, uni_one, uni_two, default_null, updated FROM _temp_table;
DROP TABLE _temp_table;

SQL;

    protected $testCreateTableAfterAlter = <<<SQL
CREATE TABLE test_indexes (
 id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 name varchar(255) NOT NULL,
 uni_one INTEGER DEFAULT NULL,
 uni_two INTEGER DEFAULT NULL,
 default_null float DEFAULT NULL,
 updated timestamp DEFAULT NULL,
 new_field varchar(255) NOT NULL DEFAULT 'normal'
);
CREATE UNIQUE INDEX unique_updated ON test_indexes (updated);

SQL;


}