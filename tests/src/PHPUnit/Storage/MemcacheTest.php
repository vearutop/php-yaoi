<?php
namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Storage\Exception;
use Yaoi\Test\PHPUnit\TestCase;


class MemcacheTest extends TestCase
{

    public function setUp()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension is not available');
            return;
        }

        if (empty(TestCase::$settings['envMemcache'])) {
            $this->markTestSkipped('Memcache tests disabled');
            return;
        }
    }


    public function testMain()
    {
        //phpinfo();
        $storage = new Storage('memcache://localhost/');
        $storage->set('test1', '43434');
        $storage->set('test2', '43435');

        unset($storage);
        $storage = new Storage('memcache://localhost/');
        $this->assertSame('43434', $storage->get('test1'));
        $this->assertSame('43435', $storage->get('test2'));

        unset($storage);
        $storage = new Storage('memcache://localhost/');
        $storage->delete('test1');
        $this->assertSame(false, $storage->get('test1'));
        $this->assertSame('43435', $storage->get('test2'));

        unset($storage);
        $storage = new Storage('memcache://localhost/');
        $storage->deleteAll();
        $this->assertSame(false, $storage->get('test2'));


        $storage->set(array('complex', 'key'), 'yeah');
        $this->assertSame('yeah', $storage->get(array('complex', 'key')));
        $this->assertSame('yeah', $storage->get('complex/key'));
    }

    /**
     * @expectedException     \Yaoi\Storage\Exception
     * @expectedExceptionCode \Yaoi\Storage\Exception::EXPORT_ARRAY_NOT_SUPPORTED
     */
    public function testExportException()
    {
        if (empty(TestCase::$settings['envMemcache'])) {
            echo 'Memcache tests disabled', "\n";
            throw new Exception('', Exception::EXPORT_ARRAY_NOT_SUPPORTED);
        }

        $storage = new Storage('memcache://localhost/');
        $storage->exportArray();
    }

    /**
     * @expectedException     \Yaoi\Storage\Exception
     * @expectedExceptionCode \Yaoi\Storage\Exception::EXPORT_ARRAY_NOT_SUPPORTED
     */
    public function testImportException()
    {
        if (empty(TestCase::$settings['envMemcache'])) {
            echo 'Memcache tests disabled', "\n";
            throw new Exception('', Exception::EXPORT_ARRAY_NOT_SUPPORTED);
        }

        $storage = new Storage('memcache://localhost/');
        $storage->importArray(array(1, 2, 3));
    }

    public function testScalar()
    {
        // TODO bug in memcached.so?
        return;
        $storage = new Storage('memcache://localhost/');
        $storage->deleteAll();
        $storage->set('string', '1234');
        $this->assertSame('1234', $storage->get('string'));
        $storage->set('int', 1234);
        $this->assertSame(1234, $storage->get('int'));
    }

    public function testComplex()
    {
        if (empty(TestCase::$settings['envMemcache'])) {
            echo 'Memcache tests disabled', "\n";
            return;
        }

        $storage = new Storage('memcache://localhost/');
        $a = array('a' => 1);
        $storage->set('object1', (object)$a);
        $storage->set('array1', $a);
        $this->assertEquals((object)$a, $storage->get('object1'));
        $this->assertSame($a, $storage->get('array1'));
    }
}