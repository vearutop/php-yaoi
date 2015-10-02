<?php

namespace YaoiTests\PHPUnit\Date;

use Yaoi\Date\TimeMachine;
use Yaoi\Test\PHPUnit\TestCase;

class TimeMachineTest extends TestCase
{

    /**
     * @throws \Yaoi\Service\Exception
     *
     * @see TimeMachine::microNow()
     */
    public function testMicroNow() {
        $timeMachine = TimeMachine::getInstance();
        $microNow = $timeMachine->microNow();
        $this->assertEquals(round($microNow), round(microtime(1)));
    }

    /**
     * @throws \Yaoi\Service\Exception
     *
     * @see TimeMachine::now()
     */
    public function testNow() {
        $timeMachine = TimeMachine::getInstance();
        $now = $timeMachine->now();
        $this->assertLessThanOrEqual($now, time());
    }

}