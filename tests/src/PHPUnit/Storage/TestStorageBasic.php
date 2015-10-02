<?php

namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Storage\Contract\Expire;
use Yaoi\Storage\Contract\ExportImportArray;
use Yaoi\Test\PHPUnit\TestCase;


abstract class TestStorageBasic extends TestCase
{
    /**
     * @var Storage
     */
    protected $storage;

    public function testTtl()
    {
        if (!$this->storage->getDriver() instanceof Expire) {
            return;
        }

        $key = 'test-key';
        $value = 'the-value';

        $this->storage->set($key, $value, 10);
        $this->assertSame($value, $this->storage->get($key));

        $this->storage->set($key, $value, 10);
        $this->assertSame(true, $this->storage->keyExists($key));

        $this->storage->set($key, $value, -1);
        $this->assertSame(null, $this->storage->get($key));


        $this->storage->set($key, $value, -1);
        $this->assertSame(false, $this->storage->keyExists($key));

    }


    public function testScalar()
    {
        $key = 'test-key';
        $key2 = array('test-key2', 'sub1', 'sub2');
        $value = 'the-value';
        $value2 = 'the-value-2';

        $this->storage->set($key, $value);
        $this->assertSame($value, $this->storage->get($key));

        $this->storage->set($key, $value2);
        $this->assertSame($value2, $this->storage->get($key));

        $this->storage->delete($key);
        $this->assertSame(null, $this->storage->get($key));

        $this->storage->set($key, $value);
        $this->storage->set($key2, $value);

        $this->assertSame($value, $this->storage->get($key));
        $this->assertSame($value, $this->storage->get($key2));

        $this->storage->deleteAll();
        $this->assertSame(null, $this->storage->get($key));
        $this->assertSame(null, $this->storage->get($key2));
    }

    public function testStrictNumeric()
    {
        $this->storage->set('test', 123123);
        $this->assertSame(123123, $this->storage->get('test'));


        $this->storage->set('test', '123123');
        $this->assertSame('123123', $this->storage->get('test'));
    }

    public function testArrayIO()
    {
        if (!$this->storage->getDriver() instanceof ExportImportArray) {
            return;
        }
        $this->storage->importArray(array('a' => 1, 'b' => 2, 'c' => 3));
        $this->storage->set('d', 4);
        $this->assertSame(array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4), $this->storage->exportArray());
    }


}