<?php

namespace YaoiTests\PHPUnit\Mock;

use Yaoi\Database;
use Yaoi\Migration\Manager;
use Yaoi\Migration\Needed;
use Yaoi\Storage;
use Yaoi\Storage\Driver\DatabaseProxy;
use Yaoi\Storage\Driver\JsonProxy;
use Yaoi\Storage\Settings;
use YaoiTests\PHPUnit\Mock\VarTest;

class SqliteTest extends VarTest
{
    /**
     * @var Storage
     */
    private $storage;

    protected function resetStorage()
    {
        if (null === $this->storage) {
            $db = new Database('sqlite:///' . sys_get_temp_dir() . '/test-sqlite.sqlite');

            //$db->log(new Log('stdout'));

            $dsn = new Settings();
            $dsn->proxyClient = $db;
            //$dsn->path = 'storage';
            $dsn->driverClassName = DatabaseProxy::className();

            $storage = new Storage($dsn);
            $driver = $storage->getDriver();
            if ($driver instanceof Needed) {
                Manager::getInstance()
                    ->perform($driver->getMigration());
            }

            $jsonStorage = new Storage(function () use ($storage) {
                $dsn = new Settings();
                $dsn->driverClassName = JsonProxy::className();
                $dsn->proxyClient = $storage;
                return $dsn;
            });

            $this->storage = $jsonStorage;
        }

        $this->storage->deleteAll();
        return $this->storage;

    }

}