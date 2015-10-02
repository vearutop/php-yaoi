<?php

namespace YaoiTests\PHPUnit\Storage;

use Yaoi\Storage;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;


/**
 * Class ClientTest
 */
class Test extends TestCase
{
    private $client;

    public function testChaining()
    {
        $this->client = Storage::create('php-var://localhost');

        $this->assertSame(
            $this->client
                ->set(1, 2)
                ->set(2, 3)
                ->getIn(2, $v)
                ->delete(2)
                ->deleteAll()
            ,
            $this->client);
    }

    public function testSetOnMiss()
    {
        $storage = new PhpVar();

        $sets = 0;

        for ($i = 0; $i < 10; ++$i) {
            $storage->get('test', function () use (&$sets) {
                ++$sets;
                return 123123;
            });
        }

        $this->assertSame(1, $sets);
        $this->assertSame(123123, $storage->get('test'));
    }
}