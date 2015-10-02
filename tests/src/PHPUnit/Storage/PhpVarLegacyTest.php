<?php

namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Test\PHPUnit\TestCase;


class PhpVarLegacyTest extends TestCase
{
    /** @var Storage  */
    private $client;

    public function setUp()
    {
        $this->client = Storage::create('php-var://localhost');
    }

    public function testArrayKey()
    {
        $this->client->deleteAll();
        $this->client->set(array('test1', 'test2', 'test3'), 123123);
        $this->client->set(array('test1', 'test4', 'test5'), 323123);

        $this->assertSame($this->client->get(array('test1', 'test2', 'test3')),
            123123);

        $v = $this->client->get(null);

        $this->assertSame($v['test1']['test2']['test3'], 123123);

        $this->assertSame($this->client->get(array('test1', 'test2')), array('test3' => 123123));

        $this->client->delete(array('test1', 'test2'));
        $this->assertSame($this->client->get(array('test1', 'test2')), null);
        $this->assertSame($this->client->get(array('test1', 'test4')), array('test5' => 323123));


        $this->assertSame($this->client->get(null), $this->client->delete(array('test1', 'test6'))->get(null));

        $this->assertNotSame($this->client->get(null), $this->client->delete(array('test1', 'test4'))->get(null));

        $this->client->delete('test1');
        $this->assertSame($this->client->get('test1'), null);
    }


    public function testArrayIO()
    {
        $s = Storage::create('php-var://dummy');
        $s->importArray(array('a' => 1, 'b' => 2, 'c' => 3));
        $s->set('d', 4);
        $this->assertSame(array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4), $s->exportArray());
    }
}