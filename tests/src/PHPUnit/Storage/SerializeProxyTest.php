<?php
namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Storage\Driver\PhpVarExpire;
use Yaoi\Storage\Driver\SerializeProxy;
use Yaoi\Storage\Settings;

class SerializeProxyTest extends TestStorageBasic
{
    /**
     * @var Storage
     */
    protected $base;

    protected function initStorage()
    {
        $base = $this->base;
        $this->storage = new Storage(function () use ($base) {
            $dsn = new Settings();
            $dsn->driverClassName = SerializeProxy::className();
            $dsn->proxyClient = $base;
            return $dsn;
        });
    }

    public function setUp()
    {
        $this->base = new Storage(function () {
            $dsn = new Settings();
            $dsn->driverClassName = PhpVarExpire::className();
            return $dsn;
        });
        $this->initStorage();
    }

    public function testData()
    {
        $key = 'test';
        $value = array(1 => array(2 => array(3 => 'four')));

        $this->storage->set($key, $value);
        $this->assertSame($value, $this->storage->get($key));
    }

}