<?php

namespace YaoiTests\PHPUnit\Base;

use Yaoi\Test\PHPUnit\TestCase;

class Test extends TestCase
{

    public function testBaseCreate()
    {
        $test = new \YaoiTests\Helper\Base\BaseTest1(1, 2, 3, 4, 5);

        $this->assertSame($test->amount, \YaoiTests\Helper\Base\BaseTest1::create(1, 2, 3, 4, 5)->amount);
    }

}