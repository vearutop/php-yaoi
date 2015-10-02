<?php
namespace YaoiTests\PHPUnit\Database;

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\App;

/**
 * Class DatabaseBasicTest
 * @deprecated
 */
class Test extends TestCase
{

    public function setUp()
    {
        try {
            if (!class_exists('mysqli', false)) {
                throw new \Exception('Mysqli extension is not available');
            }
            Database::getInstance('test_mysqli');
        } catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }


    public function initTest1()
    {
        /*
        Migration_Client::getInstance()
            ->add('test1',
                function () {
                    $db = App::db('test_mysqli')->mock();
                    $db->query("DROP TABLE IF EXISTS test1");
                    $db->query("CREATE TABLE test1 (id integer unsigned auto_increment, PRIMARY KEY(id)) ENGINE=InnoDB");
                    $db->query("INSERT INTO test1 (id) VALUES (1),(2),(3)");
                }, function () {
                    return false;
                }
            );
        */

        $database = App::database('test_mysqli')->mock();
        $database->query("DROP TABLE IF EXISTS test1");
        $database->query("CREATE TABLE test1 (id integer unsigned auto_increment, PRIMARY KEY(id)) ENGINE=InnoDB");
        $database->query("INSERT INTO test1 (id) VALUES (1),(2),(3)");
    }

    public function testConnection()
    {
        $database = Database::getInstance('test_mysqli');
        $res = $database->query("SHOW VARIABLES")->fetchAll();

        $this->assertFalse(empty($res));
    }


    public function testFetch()
    {
        $this->initTest1();

        $db = Database::getInstance('test_mysqli');
        $row = $db->query("SELECT * FROM test1 ORDER BY id ASC")->fetchRow();
        $this->assertSame(array('id' => '1'), $row);

        $res = $db->query("SELECT * FROM test1 ORDER BY id ASC");
        $a1 = $res->fetchAll();
        $this->assertSame(array(array('id' => '1'), array('id' => '2'), array('id' => '3')), $a1);

        $a2 = $res->fetchAll('id');
        $this->assertSame(array(1 => array('id' => '1'), 2 => array('id' => '2'), 3 => array('id' => '3')), $a2);
    }

    public function testFetchIterator()
    {
        $this->initTest1();
        $db = Database::getInstance('test_mysqli');

        $query = $db->query("SELECT * FROM test1 ORDER BY id ASC");
        $this->assertSame(0, $query->key());

        $expected = array(array('id' => '1'), array('id' => '2'), array('id' => '3'));
        $this->assertSame($expected, $query->fetchAll());
        $this->assertSame($expected, $query->fetchAll());

        // check correct rewind after fetching
        $res = array();
        foreach ($query as $row) {
            $res [] = $row;
        }
        $this->assertSame($expected, $res);

        // check correct rewind after iterating
        $res = array();
        $lastKey = null;
        foreach ($query as $key => $row) {
            $res [] = $row;
            $lastKey = $key;
        }
        $this->assertSame($expected, $res);
        $this->assertSame(2, $lastKey);


    }

    public function testBinds()
    {
        $db = Database::getInstance('test_mysqli');
        $this->initTest1();

        $query = $db->query("SELECT id, :id, :st, :nu FROM test1 WHERE id = :id",
            array('id' => 2, 'st' => 'ss', 'nu' => null))->skipAutoExecute();
        $this->assertSame("SELECT id, 2, 'ss', NULL FROM test1 WHERE id = 2", $query->build());
    }


    public function testQuote()
    {
        $db = Database::getInstance('test_mysqli');

        // special characters escaping
        $this->assertSame('\'ab\"?/\\\'232' . "\t" . '\r\n\'', $db->quote("ab\"?/'232\t\r\n"));

        // no hyphens for int
        $this->assertSame('5', $db->quote(5));

        // floats
        $this->assertSame('50000000000000', $db->quote(50000000000000));
        $this->assertSame('50000000000.555', $db->quote(50000000000.555));
        $this->assertSame('5.555', $db->quote(5.555));

        $this->assertSame("'test'", $db->quote('test'));

        $this->assertSame('NULL', $db->quote(null));

        $this->assertSame('1, 2, 3, \'five\', NULL', $db->quote(array(1, 2, 3, 'five', null)));
    }

    /**
     * @expectedException     \Yaoi\Database\Exception
     * @expectedExceptionCode \Yaoi\Database\Exception::QUERY_ERROR
     */
    public function testBadQuery()
    {
        Database::getInstance('test_mysqli')
            ->mock()
            ->query("SELECT * FROM no_table")
            ->execute();
    }


    public function testDestructExecute()
    {
        $db = Database::getInstance('test_mysqli')->mock();
        $db->query("DELETE FROM test1");
        $db->query("INSERT INTO test1 (id) VALUES (777)");
        $this->assertSame('777', $db->query("SELECT id FROM test1 WHERE id=777")->fetchRow('id'));
    }

    public function testLastInsertId()
    {
        $db = Database::getInstance('test_mysqli')->mock();
        $this->initTest1();
        $query = $db->query("INSERT INTO test1 (id) VALUES (23)");
        $this->assertSame(23, $query->lastInsertId());

        $query = $db->query("INSERT INTO test1 () VALUES ()");
        $this->assertSame(24, $query->lastInsertId());
    }

    public function testRowsAffected()
    {
        $db = Database::getInstance('test_mysqli');
        $this->initTest1();
        $db->query("UPDATE test1 SET id=id+10 WHERE id >= 2")->rowsAffectedIn($var);

        $this->assertSame(2, $var);
    }

    /**
     * @expectedException     \Yaoi\Service\Exception
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_DRIVER
     */
    public function testNoDriver()
    {
        Database::create('no-driver://localhost')->query("TEST")->execute();
    }


    /**
     * TODO
     * @exp2ectedException     \Yaoi\Database\Exception
     * @exp2ectedExceptionCode \Yaoi\Database\Exception::CONNECTION_ERROR
     */
    public function testNoConnection()
    {
        //Database_Client::create('mysqli://127.0.0.1/test')->query("TEST");
    }


    public function testIterateEmpty()
    {
        $db = Database::getInstance('test_mysqli');
        $res = $db->query("SELECT * FROM test1 WHERE 1>2");
        $result = array();
        foreach ($res as $row) {
            $result [] = $row;
        }
        $this->assertSame(array(), $result);
    }


    public function testGetColumns()
    {
        $db = Database::getInstance('test_mysqli');
        $db->query("CREATE TABLE IF NOT EXISTS test_columns (
a1 int,
a2 tinyint,
a3 smallint,
a4 mediumint,
a5 bigint,
a6 decimal(1,1),
a7 numeric(1,1),
a8 float,
a9 real,
a10 double,
a11 text,
a12 char(10),
a13 VARCHAR(255),
a14 TIMESTAMP,
a15 date,
a16 datetime,
a17 time
)");

        $columnFlags = array();
        foreach ($db->getTableDefinition('test_columns')->getColumns(true) as $column) {
            $columnFlags[$column->propertyName] = $column->flags;
        }

        $this->assertArrayBitwiseAnd(array(
            'a1' => Column::INTEGER,
            'a2' => Column::INTEGER,
            'a3' => Column::INTEGER,
            'a4' => Column::INTEGER,
            'a5' => Column::INTEGER,
            'a6' => Column::FLOAT,
            'a7' => Column::FLOAT,
            'a8' => Column::FLOAT,
            'a9' => Column::FLOAT,
            'a10' => Column::FLOAT,
            'a11' => Column::STRING,
            'a12' => Column::STRING,
            'a13' => Column::STRING,
            'a14' => Column::TIMESTAMP,
            'a15' => Column::TIMESTAMP,
            'a16' => Column::TIMESTAMP,
            'a17' => Column::STRING
        ), $columnFlags);
    }


}