<?php

namespace YaoiTests\PHPUnit\View;

use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\View\Void;
use Yaoi\View\Raw;
use Yaoi\View\Stack;


class StackTest extends TestCase
{
    public function testStack()
    {
        $this->assertSame('onetwothree', (string)Stack::create()
            ->push(Raw::create('one'))
            ->push(Raw::create('two'))
            ->push(Raw::create('three')));

        $this->assertSame(true, Stack::create()->isEmpty());
        $this->assertSame(false, Stack::create()->push(Void::create())->isEmpty());
    }
}