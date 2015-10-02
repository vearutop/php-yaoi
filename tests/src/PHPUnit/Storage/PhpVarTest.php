<?php
namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Storage\Driver\PhpVar;
use Yaoi\Storage\Settings;

class PhpVarTest extends TestStorageBasic
{
    public function setUp()
    {
        $this->storage = Storage::getInstance(function () {
            $dsn = new Settings();
            $dsn->driverClassName = PhpVar::className();
            return $dsn;
        });
    }

}