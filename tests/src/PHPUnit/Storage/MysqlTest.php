<?php
namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Database;
use Yaoi\Migration\ClosureMigration;
use Yaoi\Migration\Manager;
use Yaoi\Migration\Migration;
use Yaoi\Migration\Needed;
use Yaoi\Storage;
use Yaoi\Storage\Driver\DatabaseProxy;
use Yaoi\Storage\Settings;
use YaoiTests\PHPUnit\Storage\TestStorageBasic;


class MysqlTest extends TestStorageBasic
{
    protected $storage;
    protected $complexStorage;
    /** @var  Database */
    protected $db;

    public function setUp()
    {
        try {
            $this->db = Database::getInstance('test_mysqli');
        } catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        //$db->log(new Log('stdout'));

        $dsn = new Settings();
        $dsn->proxyClient = $this->db;
        //$dsn->path = 'storage_table';
        $dsn->driverClassName = DatabaseProxy::className();

        $this->storage = new Storage($dsn);
        $driver = $this->storage->getDriver();
        if ($driver instanceof Needed) {
            Manager::getInstance()
                ->perform($driver->getMigration());
        }


        /*
        $this->complexStorage = new Storage(function () {
            $dsn = new Storage_Dsn();
            $dsn->driverClassName = Storage_Driver_JsonProxy::className();
            $dsn->proxyClient = $this->storage;
            return $dsn;
        });
*/

    }

    public function testConstructor()
    {
        //$db->log(new Log('stdout'));

        $dsn = new Settings();
        $dsn->proxyClient = $this->db;
        $dsn->path = 'storage_table';
        $dsn->driverClassName = DatabaseProxy::className();

        $storage = new Storage($dsn);
        $driver = $storage->getDriver();

        $this->assertSame(DatabaseProxy::className(), get_class($driver));
    }


    /**
     * @expectedException \Yaoi\Storage\Exception
     * @expectedExceptionCode \Yaoi\Storage\Exception::PROXY_REQUIRED
     */
    public function testProxyRequired()
    {
        $dsn = new Settings();
        $dsn->driverClassName = DatabaseProxy::className();

        $storage = new Storage($dsn);
        $driver = $storage->getDriver();

        $this->assertSame(DatabaseProxy::className(), get_class($driver));
    }

    /**
     * @expectedException \Yaoi\Storage\Exception
     * @expectedExceptionCode \Yaoi\Storage\Exception::SCALAR_REQUIRED
     */
    public function testScalarRequired()
    {
        $this->storage->set('key', array(1, 2, 3));
    }


    public function testMigration()
    {
        $driver = $this->storage->getDriver();
        if ($driver instanceof Needed) {
            Manager::getInstance()
                ->perform($driver->getMigration())
                ->perform($driver->getMigration(), Migration::ROLLBACK)
                ->perform($driver->getMigration());
        }


    }

    public function testStrictNumeric()
    {
        // not working
    }
    /*
        public function testTwo()
        {
            return;

            $this->complexStorage->set('test2', array('vvvv' => 'hooy'));

            var_dump($this->storage->get('test1'));
            var_dump($this->complexStorage->get('test2'));
        }
    */
}