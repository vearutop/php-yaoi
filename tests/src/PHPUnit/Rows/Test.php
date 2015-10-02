<?php
namespace YaoiTests\PHPUnit\Rows;

use Yaoi\Rows\Processor;
use Yaoi\Test\PHPUnit\TestCase;


class Test extends TestCase
{
    public function testCreate()
    {
        $rows = array();
        $rows [] = array('x' => 1, 'y' => 112, 'name' => 'serie 1');
        $rows [] = array('x' => 2, 'y' => 114, 'name' => 'serie 1');
        $rows [] = array('x' => 2, 'y' => 117, 'name' => 'serie 2');
        $rows [] = array('x' => 3, 'y' => 113, 'name' => 'serie 2');

        $p = new Processor($rows);
        $p->skipField('y');
        $transformed = array();
        foreach ($p as $row) {
            $transformed [] = $row;
        }
        $this->assertSame(array(0 => array('x' => 1, 'name' => 'serie 1',), 1 => array('x' => 2, 'name' => 'serie 1',), 2 => array('x' => 2, 'name' => 'serie 2',), 3 => array('x' => 3, 'name' => 'serie 2')),
            $transformed);


        $p = new Processor($rows);
        $p->changeKey('x', 'XX')->changeKey('y', 'YY');
        $transformed = array();
        foreach ($p as $row) {
            $transformed [] = $row;
        }
        $this->assertSame(array(0 => array('XX' => 1, 'YY' => 112, 'name' => 'serie 1',), 1 => array('XX' => 2, 'YY' => 114, 'name' => 'serie 1',), 2 => array('XX' => 2, 'YY' => 117, 'name' => 'serie 2',), 3 => array('XX' => 3, 'YY' => 113, 'name' => 'serie 2')),
            $transformed);


        $transformed = array();
        foreach (Processor::create($rows)->combine('name', 'y') as $row) {
            $transformed [] = $row;
        }
        $this->assertSame(array(0 => array('x' => 1, 'serie 1' => 112,), 1 => array('x' => 2, 'serie 1' => 114,), 2 => array('x' => 2, 'serie 2' => 117,), 3 => array('x' => 3, 'serie 2' => 113)),
            $transformed);


        $rows2 = array();
        $rows2 [] = array(1, 'y1' => 112, 'name1' => 'serie 1');
        $rows2 [] = array(2, 'y2' => 114, 'name2' => 'serie 1');
        $rows2 [] = array(2, 'y3' => 117, 'name3' => 'serie 2');
        $rows2 [] = array(3, 'y4' => 113, 'name4' => 'serie 2');

        $transformed = array();
        foreach (Processor::create($rows)->combineOffset(2, 1) as $row) {
            $transformed [] = $row;
        }
        $this->assertSame(array(0 => array('x' => 1, 'serie 1' => 112,), 1 => array('x' => 2, 'serie 1' => 114,), 2 => array('x' => 2, 'serie 2' => 117,), 3 => array('x' => 3, 'serie 2' => 113)),
            $transformed);


    }


    public function testIterator()
    {
        $rows = array(array(1, 2, 3), array(4, 5, 6));
        $a = new \AppendIterator();
        $a->append(new \ArrayIterator($rows));
        $a->append(new \ArrayIterator($rows));

        $r2 = Processor::create($a);
        //print_r($r2->exportArray());

        $expected = array(array(1, 2, 3), array(4, 5, 6), array(1, 2, 3), array(4, 5, 6));
        $this->assertSame($expected, $r2->exportArray());
    }

}