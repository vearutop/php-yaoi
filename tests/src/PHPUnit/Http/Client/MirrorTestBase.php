<?php

namespace YaoiTests\PHPUnit\Http\Client;


use Yaoi\Test\PHPUnit\TestCase;

class MirrorTestBase extends TestCase
{
    public function setUp() {
        if (empty(TestCase::$settings['envHttpMirrorServer'])) {
            $this->markTestSkipped('HttpMirror tests disabled');
            return;
        }

    }

}