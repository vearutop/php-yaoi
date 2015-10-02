<?php
namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Storage\Driver\PhpVarExpire;
use Yaoi\Storage\Settings;
use YaoiTests\PHPUnit\Storage\TestStorageBasic;

class PhpVarExpireTest extends TestStorageBasic
{
    public function setUp()
    {
        $this->storage = Storage::getInstance(function () {
            $dsn = new Settings();
            $dsn->driverClassName = PhpVarExpire::className();
            return $dsn;
        });
    }

}