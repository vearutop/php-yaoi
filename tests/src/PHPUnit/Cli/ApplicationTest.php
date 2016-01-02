<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Command\Application\Runner;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestApplication;
use YaoiTests\Helper\Command\TestCommandOne;
use YaoiTests\Helper\TestRequestHelper;

class ApplicationTest extends TestCase
{
    public function testHelp() {
        $expected = "\x1B" . '[36;1mv1.0 test-application' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mTest application description' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '   test-application <action>' . PHP_EOL
            . '   ' . "\x1B" . '[32;1maction' . "\x1B" . '[m   Action name                                      ' . PHP_EOL
            . '            Allowed values: actionOne, actionTwo, actionThree' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mMisc: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help            ' . "\x1B" . '[m   Show usage information    ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version         ' . "\x1B" . '[m   Show version              ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--bash-completion ' . "\x1B" . '[m   Generate bash completion  ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--install         ' . "\x1B" . '[m   Install to /usr/local/bin/' . PHP_EOL
            . '   ' . PHP_EOL;

        ob_start();
        Runner::create(new TestApplication)->run(TestRequestHelper::getCliRequest(array('--help')));
        $result = ob_get_clean();
        //echo $this->varExportString($result);die();
        $this->assertSame($expected, $result);
    }

    public function testCommandException()
    {
        $expected = "\x1B" . '[37;41m Application required ' . "\x1B" . '[m' . PHP_EOL
            . 'Use --help to show information.' . PHP_EOL;

        ob_start();
        Runner::create(new TestCommandOne())->run();
        $result = ob_get_clean();

        //echo $this->varExportString($result);die();

        $this->assertSame($expected, $result);
    }

    public function testActionHelp()
    {
        $expected = "\x1B" . '[36;1mv1.0 test-application actionOne' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mTest application description' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '   test-application actionOne <argument> <argumentTwo...>' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margument   ' . "\x1B" . '[m   ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentTwo' . "\x1B" . '[m   ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mOptions: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--option ' . "\x1B" . '[m   ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mMisc: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help            ' . "\x1B" . '[m   Show usage information    ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version         ' . "\x1B" . '[m   Show version              ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--bash-completion ' . "\x1B" . '[m   Generate bash completion  ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--install         ' . "\x1B" . '[m   Install to /usr/local/bin/' . PHP_EOL
            . '   ' . PHP_EOL;


        ob_start();
        Runner::create(new TestApplication)->run(TestRequestHelper::getCliRequest(array('actionOne', '--help')));
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);die();
        $this->assertSame($expected, $result);
    }

    public function testVersion()
    {
        $expected = "\x1B" . '[36;1mv1.0 test-application' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[36;1mTest application description' . "\x1B" . '[m' . PHP_EOL;

        ob_start();
        Runner::create(new TestApplication)->run(TestRequestHelper::getCliRequest(array('--version')));
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);die();
        $this->assertSame($expected, $result);
    }

}