<?php
namespace YaoiTests\PHPUnit\Database;

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Test\PHPUnit\TestCase;

class SqliteLegacyTest extends TestCase
{

    /** @var  Database */
    private $db;

    private $sqliteFileName;

    public function setUp()
    {
        $this->sqliteFileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-sqlite-5.sqlite';
        $this->db = new Database('sqlite:///' . $this->sqliteFileName);

        //var_dump($this->db->getDriver());
        $this->db->query("DROP TABLE IF EXISTS test1");
        $this->db->query("CREATE TABLE test1 (id integer unsigned auto_increment, PRIMARY KEY(id))");
        $this->db->query("INSERT INTO test1 (id) VALUES (1)");
        $this->db->query("INSERT INTO test1 (id) VALUES (2)");
        $this->db->query("INSERT INTO test1 (id) VALUES (3)");
        //http://stackoverflow.com/questions/1609637/is-it-possible-to-insert-multiple-rows-at-a-time-in-an-sqlite-database
    }

    public function tearDown()
    {
        if (file_exists($this->sqliteFileName)) {
            unset($this->db);
            unlink($this->sqliteFileName);
        }
    }

    public function testConnection()
    {
        $res = $this->db->query("SELECT sqlite_version();")->fetchAll();

        $this->assertFalse(empty($res));
    }


    public function testFetch()
    {
        $db = $this->db;
        $row = $db->query("SELECT * FROM test1 ORDER BY id ASC")->fetchRow();
        $this->assertSame(array('id' => 1), $row);

        $res = $db->query("SELECT * FROM test1 ORDER BY id ASC");
        $a1 = $res->fetchAll();
        $this->assertSame(array(array('id' => 1), array('id' => 2), array('id' => 3)), $a1);

        $a2 = $res->fetchAll('id');
        $this->assertSame(array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3)), $a2);
    }

    public function testFetchIterator()
    {
        $db = $this->db;

        $query = $db->query("SELECT * FROM test1 ORDER BY id ASC");
        $this->assertSame(0, $query->key());

        $expected = array(array('id' => 1), array('id' => 2), array('id' => 3));
        $this->assertSame($expected, $query->fetchAll());
        $this->assertSame($expected, $query->fetchAll());

        // check correct rewind after fetching
        $res = array();
        $i = 0;
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
        $db = $this->db;

        $query = $db->query("SELECT id, :id, :st, :nu FROM test1 WHERE id = :id",
            array('id' => 2, 'st' => 'ss', 'nu' => null))->skipAutoExecute();
        $this->assertSame("SELECT id, 2, 'ss', NULL FROM test1 WHERE id = 2", $query->build());
    }


    public function testQuote()
    {
        $db = $this->db;

        // special characters escaping
        $expected = "'ab\"?/''232\t\r\n'";
        $this->assertSame($expected, $db->quote("ab\"?/'232\t\r\n"));

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
        $db = $this->db
            ->mock()
            ->query("SELECT * FROM no_table")
            ->execute();
    }


    public function testDestructExecute()
    {
        $db = $this->db;
        $db->query("DELETE FROM test1");
        $db->query("INSERT INTO test1 (id) VALUES (777)");
        $this->assertSame(777, $db->query("SELECT id FROM test1 WHERE id=777")->fetchRow('id'));
    }

    public function testLastInsertId()
    {
        $db = $this->db;
        $query = $db->query("INSERT INTO test1 (id) VALUES (23)");
        $this->assertSame(4, $query->lastInsertId());
    }

    public function testRowsAffected()
    {
        $db = $this->db;
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
        //return;
        $db = $this->db;
        $res = $db->query("SELECT * FROM test1 WHERE 1>2");
        $result = array();
        foreach ($res as $row) {
            $result [] = $row;
        }
        $this->assertSame(array(), $result);
    }


    public function testDisconnect()
    {
        $db = new Database('sqlite:///' . sys_get_temp_dir() . '/test-sqlite2.sqlite');
        $db->disconnect();

        $db2 = new Database('sqlite:///' . sys_get_temp_dir() . '/test-sqlite3.sqlite');
        $db2->query("SELECT 1");
        $db2->disconnect();
    }


    public function testTypes()
    {
        $q = "create table if not exists tes2  (a1 int, a2 bigint, a3 mediumint, a4 tinyint, a5 ololo);";
        $db2 = new Database('sqlite:///' . sys_get_temp_dir() . '/test-sqlite4.sqlite');

        $db2->query($q);
        $db2->query("INSERT INTO tes2 VALUES (1,2,3,'4','hooy')");
        $this->assertSame(array(
            'a1' => 1,
            'a2' => 2,
            'a3' => 3,
            'a4' => 4,
            'a5' => 'hooy',
        ), $db2->query("SELECT * FROM tes2")->fetchRow());

        $columnFlags = array();
        foreach ($db2->getTableDefinition('tes2')->getColumns(true) as $column) {
            $columnFlags[$column->propertyName] = $column->flags;
        }

        $this->assertArrayBitwiseAnd(array(
            'a1' => Column::INTEGER,
            'a2' => Column::INTEGER,
            'a3' => Column::INTEGER,
            'a4' => Column::INTEGER,
            'a5' => Column::STRING,
        ), $columnFlags);

        $db2->query("DROP table tes2");


    }

}