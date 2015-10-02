<?php

namespace YaoiTests\PHPUnit\Rows;

use ArrayIterator;
use Yaoi\Rows\ClosureIterator;
use Yaoi\Rows\PageIterator;
use Yaoi\Test\PHPUnit\TestCase;

class PageIteratorTest extends TestCase
{
    public function testPages()
    {
        $a = new ArrayIterator(array(1, 2, 3));

        $aa = new ArrayIterator(array(
            $a,
            $a,
            $a,
            new ArrayIterator(array(6, 7, 8))
        ));


        $p = new PageIterator($aa);

        $result = array();
        foreach ($p as $i) {
            $result [] = $i;
        }

        $expected = array(
            0 => 1,
            1 => 2,
            2 => 3,
            3 => 1,
            4 => 2,
            5 => 3,
            6 => 1,
            7 => 2,
            8 => 3,
            9 => 6,
            10 => 7,
            11 => 8,
        );

        $this->assertSame($expected, $result);
        //var_export($result);
    }


    public function testEmpty()
    {
        $a = new ArrayIterator(array());
        $p = new PageIterator($a);

        $result = array();
        foreach ($p as $item) {
            $result [] = $item;
        }

        $this->assertSame(array(), $result);
    }

    public function testClosureIterator()
    {
        $count = 0;
        $c = function () use (&$count) {
            ++$count;
            //echo $count;
            if ($count <= 3) {
                return new ArrayIterator(array(1, 2, 3));
                //return array(1,2,3);
            } else {
                return false;
            }
        };

        /*
        for ($i = 0; $i< 10; ++$i) {
            var_dump($c());
        }
        */

        $result = array();
        $ci = new PageIterator(new ClosureIterator($c));
        foreach ($ci as $i) {
            $result [] = $i;
        }

        $expected = array(
            0 => 1,
            1 => 2,
            2 => 3,
            3 => 1,
            4 => 2,
            5 => 3,
            6 => 1,
            7 => 2,
            8 => 3,
        );


        $this->assertSame($expected, $result);
    }
}