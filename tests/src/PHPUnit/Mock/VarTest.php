<?php

namespace YaoiTests\PHPUnit\Mock;

use Yaoi\Mock;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;


class VarTest extends TestCase
{
    protected function resetStorage()
    {
        return new PhpVar();
    }


    public function testModes()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);

        // set only on miss
        $mock->mode = Mock::MODE_COMBINED;
        $this->assertSame(1, $mock->get('test', function () {
            return 1;
        }));
        $this->assertSame(1, $mock->get('test', function () {
            return 2;
        }));


        // always set
        $mock->mode = Mock::MODE_CAPTURE;
        $this->assertSame(3, $mock->get('test', function () {
            return 3;
        }));
        $this->assertSame(4, $mock->get('test', function () {
            return 4;
        }));


        // always get
        $mock->mode = Mock::MODE_PLAY;
        $this->assertSame(4, $mock->get('test', function () {
            return 5;
        }));
        $this->assertSame(4, $mock->get('test', function () {
            return 6;
        }));
    }


    public function testBranch()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);

        $mock->mode = Mock::MODE_COMBINED;

        $branch = $mock->branch('a', 'b', 'c');
        $this->assertSame(1, $branch->get('test', function () {
            return 1;
        }));


        $this->assertNotSame($mock, $branch);
        $this->assertSame($mock, $mock->branch());
    }

    public function testCombined()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);

        $values = array();
        $sets = 0;
        for ($i = 0; $i < 10; ++$i) {
            $values [] = $mock->branch('test')->get(null, function () use ($i, &$sets) {
                ++$sets;
                return $i;
            });
        }
        $this->assertSame(10, $sets);
        $this->assertSame(range(0, 9), $values);


        $values = array();
        $sets = 0;
        $mock = new Mock($storage);
        for ($i = 0; $i < 10; ++$i) {
            $values [] = $mock->branch('test')->get(null, function () use ($i, &$sets) {
                ++$sets;
                return $i;
            });
        }
        $this->assertSame(0, $sets);
        $this->assertSame(range(0, 9), $values);


        $values = array();
        $sets = 0;
        $mock = new Mock($storage);
        for ($i = 0; $i < 15; ++$i) {
            $values [] = $mock->branch('test')->get(null, function () use ($i, &$sets) {
                ++$sets;
                return $i;
            });
        }
        $this->assertSame(5, $sets);
        $this->assertSame(range(0, 14), $values);
    }


    public function testTemp()
    {
        $storage = $this->resetStorage();

        $mock = new Mock($storage);

        $branch = $mock->branch('test');
        $branch->temp('temp1', 5);
        $this->assertSame(5, $branch->temp('temp1'));


        $mock = new Mock($storage);

        $branch = $mock->branch('test');
        $this->assertSame(null, $branch->temp('temp1'));
    }


    public function testIncremental()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);
        $mock->add('test');
        $mock->add('test2');

        $mock = new Mock($storage);
        $this->assertSame('test', $mock->get());
        $this->assertSame('test2', $mock->get());
    }

    /**
     * @expectedException \Yaoi\Mock\Exception
     * @expectedExceptionCode \Yaoi\Mock\Exception::CAPTURE_REQUIRED
     */
    public function testCaptureRequired()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);
        $mock->mode = Mock::MODE_PLAY;
        $mock->add('test');
    }


    /**
     * @expectedException \Yaoi\Mock\Exception
     * @expectedExceptionCode \Yaoi\Mock\Exception::PLAY_REQUIRED
     */
    public function testPlayRequired()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);
        $mock->mode = Mock::MODE_CAPTURE;
        $mock->get('test');
    }

    /**
     * @expectedException \Yaoi\Mock\Exception
     * @expectedExceptionCode \Yaoi\Mock\Exception::KEY_NOT_FOUND
     */
    public function testRecordNotFound()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);
        $mock->get('test');
    }

    public function testComplex()
    {
        $storage = $this->resetStorage();
        $mock = new Mock($storage);

        $mock->add('t1v', 'test1');
        $mock->add(1);
        $mock->add(2);
        $mock->add('t0v', 'test2');
        $mock->add(3);
        $mock->add('t2v', 'test2');
        $mock->add(1);

        $mock->branch('q', 'w', 'e')->add('test');
        $mock->branch('q', 'w', 'e')->add('test2');

        $mock->branch('ololo')->add('23');

        unset($mock);

        $mock = new Mock($storage);

        $this->assertSame('23', $mock->branch('ololo')->get());

        $this->assertSame('t1v', $mock->get('test1'));
        $this->assertSame(1, $mock->get());
        $this->assertSame(2, $mock->get());

        $this->assertSame('test', $mock->branch('q', 'w', 'e')->get());

        $this->assertSame('t1v', $mock->get('test1'));
        $this->assertSame(3, $mock->get());
        $this->assertSame(1, $mock->get());
        $this->assertSame('t1v', $mock->get('test1'));
        $this->assertSame('t2v', $mock->get('test2'));

        $this->assertSame('test2', $mock->branch('q', 'w', 'e')->get());
    }

}