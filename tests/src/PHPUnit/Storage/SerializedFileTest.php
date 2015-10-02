<?php

namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Test\PHPUnit\TestCase;


class SerializedFileTest extends TestCase
{
    public function testStoring()
    {
        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . DIRECTORY_SEPARATOR . 'temp1.dat';
        //var_dump($filePath);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $storage = Storage::create('serialized-file:///' . $filePath);
        $storage->set('test', 11);
        unset($storage);
        $this->assertSame('a:1:{s:4:"test";i:11;}', file_get_contents($filePath));
        $storage = new Storage('serialized-file:///' . $filePath);
        $this->assertSame(11, $storage->get('test'));
        unset($storage);
        $storage = new Storage('serialized-file://localhost/' . $filePath);
        $storage->set('test2', 22);
        unset($storage);
        $storage = new Storage('serialized-file://localhost/' . $filePath);
        $storage->delete('test');
        $this->assertSame(22, $storage->get('test2'));
        unset($storage);
        $this->assertSame('a:1:{s:5:"test2";i:22;}', file_get_contents($filePath));
        $storage = new Storage('serialized-file://localhost/' . $filePath);
        $storage->deleteAll();
        $this->assertSame('a:0:{}', file_get_contents($filePath));
        unlink($filePath);
    }


    /**
     * @expectedException     \Yaoi\Storage\Exception
     * @expectedExceptionCode \Yaoi\Storage\Exception::BAD_SERIALIZED_DATA
     */
    public function testException()
    {
        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . '/temp1.dat';

        file_put_contents($filePath, 'malformed data');
        $storage = new Storage('serialized-file://localhost/' . $filePath);
        $storage->get('nonexistent');
    }
}