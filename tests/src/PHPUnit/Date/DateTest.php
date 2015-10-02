<?php

namespace YaoiTests\PHPUnit\Date;

use Yaoi\Date\TimeMachine;
use Yaoi\Mock;
use Yaoi\Storage;
use Yaoi\Test\PHPUnit\TestCase;

class DateTest extends TestCase
{

    public function testDate_20140110()
    {
        $storage = new Storage('serialized-file:///' . __DIR__ . '/../../../resources/mocked-data-sets/DateTest-Date-20140910.dat');
        //$storage = new Storage_Var();

        //print_r($storage->exportArray());

        $mockSet = new Mock($storage);
        //$mockSet->mode = Mock::MODE_CAPTURE;
        $mockSet->mode = Mock::MODE_PLAY;

        $d = new TimeMachine();
        $d->mock($mockSet);

        $this->assertSame('2014-01-10', $d->date('Y-m-d'));

        $this->assertSame('2014-01-10', $d->rusDayMonthToDate('10 я.'));
        $this->assertSame('2014-02-09', $d->rusDayMonthToDate('9 ф.'));
        $this->assertSame('2014-02-08', $d->rusDayMonthToDate('8 ш.'));
        $this->assertSame('2014-01-11', $d->rusDayMonthToDate('11 ш.'));


        $this->assertSame('2014-07-11', $d->rusDayMonthToDate('11 июля'));
        $this->assertSame('2014-01-09', $d->rusDayMonthToDate('9 января'));


        $this->assertSame(1389373200, $d->strToTime('tomorrow'));
        $this->assertSame(1389373200, $d->strToTime('tomorrow'));
        $this->assertSame(1389373200, $d->strToTime('tomorrow'));

        $this->assertSame(1389351108, $d->now());
        $this->assertSame(1389351108, $d->now());
        $this->assertSame('1389351108.0486', (string)$d->microNow());
    }


}