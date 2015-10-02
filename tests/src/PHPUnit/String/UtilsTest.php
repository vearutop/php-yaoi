<?php

namespace YaoiTests\PHPUnit\String;

use Yaoi\String\Utils;
use Yaoi\Test\PHPUnit\TestCase;


class UtilsTest extends TestCase
{
    public function testCamelCase()
    {
        $this->assertSame('lala-lala', Utils::fromCamelCase('LalaLala', '-'));
        $this->assertSame('data_abbr', Utils::fromCamelCase('DataABBR'));
        $this->assertSame('eshop', Utils::fromCamelCase('EShop'));
        $this->assertSame('LalaLala', Utils::toCamelCase('lala-lala', '-'));
    }


    public function testStartsEnds()
    {
        $this->assertSame(true, Utils::starts('the string', 'the'));
        $this->assertSame(false, Utils::starts('the string', 'string'));
        $this->assertSame(false, Utils::ends('the string', 'the'));
        $this->assertSame(true, Utils::ends('the string', 'string'));
    }

}