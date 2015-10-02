<?php

namespace YaoiTests\PHPUnit\Log;

use App;
use Yaoi;
use Yaoi\Log;
use Yaoi\Log\Settings;
use Yaoi\Storage;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;


class LogStorage extends TestCase
{
    public function testLog()
    {
        $db = Yaoi\Database::getInstance('test_mysqli')->mock();
        $storage = new PhpVar();
        $logDsn = new Settings('storage');
        $logDsn->storage = $storage;
        $db->log(new Log($logDsn));

        $db->query("SELECT NOW()");
        $db->query("SELECT UNIX_TIMESTAMP()");

        $logData = $storage->exportArray();
        $this->assertStringEndsWith('(1) SELECT NOW()', $logData[0]);
        $this->assertStringEndsWith('(1) SELECT UNIX_TIMESTAMP()', $logData[1]);

        $db->log();
    }

}