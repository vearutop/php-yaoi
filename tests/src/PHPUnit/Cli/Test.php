<?php

namespace YaoiTests\PHPUnit\Cli;

use Yaoi\Cli\Command\RequestReader;
use Yaoi\Cli\Command\Runner;
use Yaoi\Cli\Response;
use Yaoi\Io\Request;
use Yaoi\Io\Request\Server;
use Yaoi\String\Expression;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestCommandOne;
use YaoiTests\Helper\Command\TestCommandWithNonTailingOptionalArgument;
use YaoiTests\Helper\Command\TestCommandWithOptionValue;
use YaoiTests\Helper\Command\TestCommandWithRequiredArgument;
use YaoiTests\Helper\Command\TestCommandWithSuccessMessage;
use YaoiTests\Helper\Command\TestCommandWithVariadicError;
use YaoiTests\Helper\Command\TestCommandWithVersion;
use YaoiTests\Helper\TestRequestHelper;

class Test extends TestCase
{

    public function testHelp() {
        ob_start();
        Runner::create(new TestCommandOne())->showHelp();
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);die();
        $expected = "\x1B" . '[36;1mthe-first' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mThis is a command one for doing nothing' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '   the-first <action> [argumentA] [argumentB...] -d <optionD...> --some-enum <one|two|three>' . PHP_EOL
            . '   ' . "\x1B" . '[32;1maction   ' . "\x1B" . '[m   Main action                        ' . PHP_EOL
            . '               Allowed values: get, delete, create' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentA' . "\x1B" . '[m   Bee description follows            ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentB' . "\x1B" . '[m   This is a variadic argument        ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mOptions: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--option-c                 ' . "\x1B" . '[m   Some option for the C                ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m-d <optionD...>            ' . "\x1B" . '[m   Short name option with required value' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--some-enum <one|two|three>' . "\x1B" . '[m   Enumerated option to set up something' . PHP_EOL
            . '                                 Allowed values: one, two, three      ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mMisc: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help            ' . "\x1B" . '[m   Show usage information    ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version         ' . "\x1B" . '[m   Show version              ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--bash-completion ' . "\x1B" . '[m   Generate bash completion  ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--install         ' . "\x1B" . '[m   Install to /usr/local/bin/' . PHP_EOL
            . '   ' . PHP_EOL;

        $this->assertSame($expected, $result);
    }

    public function testHelpVariadicError() {
        ob_start();
        Runner::create(new TestCommandWithVariadicError)->showHelp();
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);
        $this->assertSame(
            "\x1B" . '[37;41m Command definition error: Non-tailing variadic argument [variadicArgument...] ' . "\x1B" . '[m' . PHP_EOL
            , $result);

    }

    public function testSuccessMessage() {
        ob_start();
        Runner::create(new TestCommandWithSuccessMessage)->run(TestRequestHelper::getCliRequest(array()));
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);
        $this->assertSame("\x1B" . '[30;42m Congratulations! ' . "\x1B" . '[m' . PHP_EOL, $result);

    }

    public function testInit() {
        Runner::create(new TestCommandOne)->run(TestRequestHelper::getCliRequest(array('get','123A','456B', '789B',
            '-d', 'd1', 'd2', 'd3',
            '--option-c',
            '--some-enum', 'two')));
    }


    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::OPTION_REQUIRED
     */
    public function testOptionRequired() {
        RequestReader::create()->read(TestRequestHelper::getCliRequest(array('get','123A','456B', '789B',
            //'-d', 'd1', 'd2', 'd3',
            '--option-c',
            '--some-enum', 'two')), (array)TestCommandOne::definition()->options);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::VALUE_REQUIRED
     */
    public function testArgumentRequired() {
        RequestReader::create()->read(TestRequestHelper::getCliRequest(array('get',
            '-d',
            '--option-c',
            '--some-enum', 'two')), (array)TestCommandOne::definition()->options);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::VALUE_REQUIRED
     */
    public function testArgumentRequired2() {
        RequestReader::create()->read(TestRequestHelper::getCliRequest(array(
            '--value-option', //'value',
            '--bool-option',
        )), (array)TestCommandWithOptionValue::definition()->options);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredEmpty() {
        RequestReader::create()->read(
            TestRequestHelper::getCliRequest(array()),
            (array)TestCommandWithRequiredArgument::definition()->options
        );
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredMissing() {
        RequestReader::create()->read(
            TestRequestHelper::getCliRequest(array('arg1')),
            (array)TestCommandWithRequiredArgument::definition()->options
        );
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredOptionFound() {
        RequestReader::create()->read(
            TestRequestHelper::getCliRequest(array('arg1', '--option')),
            (array)TestCommandWithRequiredArgument::definition()->options
        );
    }

    public function testVariadicArgument() {
        $command = new TestCommandWithRequiredArgument;
        Runner::create($command)->run(TestRequestHelper::getCliRequest(array('arg1', 'arg2a', 'arg2b')));
        $this->assertSame(array('arg2a', 'arg2b'), $command->argumentTwo);
    }

    public function testUnifiedOption() {
        ob_start();
        Runner::create(new TestCommandWithOptionValue)->showHelp();
        $result = ob_get_clean();
        //echo $this->varExportString($result);die();
        $expected = "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mOptions: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--value-option <valueOption>' . "\x1B" . '[m   ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--bool-option               ' . "\x1B" . '[m   ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--unified-option            ' . "\x1B" . '[m   ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mMisc: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help            ' . "\x1B" . '[m   Show usage information    ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version         ' . "\x1B" . '[m   Show version              ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--bash-completion ' . "\x1B" . '[m   Generate bash completion  ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--install         ' . "\x1B" . '[m   Install to /usr/local/bin/' . PHP_EOL
            . '   ' . PHP_EOL;
        $this->assertSame($expected, $result);

    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::UNKNOWN_OPTION
     */
    public function testUnknownOption() {
        RequestReader::create()->read(TestRequestHelper::getCliRequest(array(
            '--value-option', 'value',
            '--bool-option',
            '--unknown-option'
        )), (array)TestCommandWithOptionValue::definition()->options);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::NON_TAILING_OPTIONAL_ARGUMENT
     */
    public function testNonTailingOptionalArgument() {
        RequestReader::create()->read(
            TestRequestHelper::getCliRequest(array('get','123A','456B', '789B',
            //'-d', 'd1', 'd2', 'd3',
            '--option-c', 'THE-C-VALUE',
            '--some-enum', 'two')),
            (array)TestCommandWithNonTailingOptionalArgument::definition()->options
        );
    }


    public function testHelpRun() {
        ob_start();
        Runner::create(new TestCommandOne)->run(TestRequestHelper::getCliRequest(array('--help')));
        $result = ob_get_clean();
        //echo $this->varExportString($result);die();
        $expected = "\x1B" . '[36;1mthe-first' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mThis is a command one for doing nothing' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '   the-first <action> [argumentA] [argumentB...] -d <optionD...> --some-enum <one|two|three>' . PHP_EOL
            . '   ' . "\x1B" . '[32;1maction   ' . "\x1B" . '[m   Main action                        ' . PHP_EOL
            . '               Allowed values: get, delete, create' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentA' . "\x1B" . '[m   Bee description follows            ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentB' . "\x1B" . '[m   This is a variadic argument        ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mOptions: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--option-c                 ' . "\x1B" . '[m   Some option for the C                ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m-d <optionD...>            ' . "\x1B" . '[m   Short name option with required value' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--some-enum <one|two|three>' . "\x1B" . '[m   Enumerated option to set up something' . PHP_EOL
            . '                                 Allowed values: one, two, three      ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mMisc: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help            ' . "\x1B" . '[m   Show usage information    ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version         ' . "\x1B" . '[m   Show version              ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--bash-completion ' . "\x1B" . '[m   Generate bash completion  ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--install         ' . "\x1B" . '[m   Install to /usr/local/bin/' . PHP_EOL
            . '   ' . PHP_EOL;
        $this->assertSame($expected, $result);
    }

    public function testVersionRun() {
        ob_start();
        Runner::create(new TestCommandWithVersion)->run(TestRequestHelper::getCliRequest(array('--version')));
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);die();
        $expected = "\x1B" . '[36;1mv1.0 cli-cli-cli' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mTest command with version' . "\x1B" . '[m' . PHP_EOL;

        $this->assertSame($expected, $result);
    }

    public function testError() {
        ob_start();
        $response = new Response();
        $response->error(new Expression("?, ?!", 'hello', 'world'));
        $response->success(new Expression("?, ?!", 'hello', 'world'));
        $response->error('hello, world!');
        $response->success('hello, world!');
        $result = ob_get_clean();
        //echo $this->varExportString($result);
        $expected = "\x1B" . '[37;41m hello, world! ' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[30;42m hello, world! ' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[37;41m hello, world! ' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[30;42m hello, world! ' . "\x1B" . '[m' . PHP_EOL;

        $this->assertSame($expected, $result);
    }
}