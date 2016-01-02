<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Command\RequestReader;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestCommandWithRequiredArgument;
use YaoiTests\Helper\Command\TestCommandWithRequiredOption;
use YaoiTests\Helper\TestRequestHelper;

class RequestReaderTest extends TestCase
{
    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredException()
    {
        $reader = new RequestReader();
        $reader->read(
            TestRequestHelper::getCliRequest('--non-existent'),
            TestCommandWithRequiredArgument::optionsArray()
        );
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::OPTION_REQUIRED
     */
    public function testOptionRequiredException()
    {
        $reader = new RequestReader();
        $reader->read(
            TestRequestHelper::getCliRequest('--optional'),
            TestCommandWithRequiredOption::optionsArray()
        );
    }

}