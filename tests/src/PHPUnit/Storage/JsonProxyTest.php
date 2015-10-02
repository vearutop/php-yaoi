<?php
namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Storage\Driver\JsonProxy;
use Yaoi\Storage\Settings;
use YaoiTests\PHPUnit\Storage\SerializeProxyTest;


class JsonProxyTest extends SerializeProxyTest
{
    protected function initStorage()
    {
        $base = $this->base;
        $this->storage = new Storage(function () use ($base) {
            $dsn = new Settings();
            $dsn->driverClassName = JsonProxy::className();
            $dsn->proxyClient = $base;
            return $dsn;
        });
    }
}