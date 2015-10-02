<?php
namespace YaoiTests\PHPUnit\Log;

use Yaoi\Log;
use Yaoi\Log\Settings;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;


class Test extends TestCase
{
    public function testLog()
    {
        ob_start();
        Log::create(new Settings('stdout'))->push('test33');
        $result = ob_get_contents();
        ob_end_clean();
        $this->assertSame('test33' . "\r\n", $result);


        ob_start();
        Log::create(new Settings('void'))->push('test33');
        $result = ob_get_contents();
        ob_end_clean();
        $this->assertSame('', $result);
    }

    public function testStorage()
    {
        $storage = new PhpVar();
        $logDsn = new Settings('storage');
        $logDsn->storage = $storage;
        $log = Log::getInstance($logDsn);

        $log->push(array('one', 'two', 'three'));

        $expected = array(
            'one' =>
                array(
                    'two' => 'three',
                ),
        );

        $this->assertSame($expected, $storage->exportArray());
    }
}