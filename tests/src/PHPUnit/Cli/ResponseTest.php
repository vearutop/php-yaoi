<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Console;
use Yaoi\Cli\Response;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\SubContent;
use Yaoi\Io\Content\Text;
use Yaoi\Test\PHPUnit\TestCase;

class ResponseTest extends TestCase
{
    protected function getResponse()
    {
        return new Response();
    }

    public function testResponse()
    {
        $this->assertInstanceOf(Console::className(), $this->getResponse()->console());
    }

    public function testRows()
    {
        $expected = 'a   b   c' . PHP_EOL
            . '1   2   3' . PHP_EOL
            . '4   5   6' . PHP_EOL
            . '7   8   9' . PHP_EOL
            . PHP_EOL;

        $rows = new \ArrayIterator(array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
            array('a' => 7, 'b' => 8, 'c' => 9),
        ));

        ob_start();
        $this->getResponse()->addContent(new Rows($rows));
        $result = ob_get_clean();

        //echo $this->varExportString($result);
        $this->assertSame($expected, $result);
    }

    public function testError()
    {
        $expected = "\x1B" . '[37;41m Unexpected expectations expected ' . "\x1B" . '[m' . PHP_EOL;

        ob_start();
        $this->getResponse()->error('Unexpected expectations expected');
        $result = ob_get_clean();

        //echo $this->varExportString($result);
        $this->assertSame($expected, $result);
    }

    public function testSuccess()
    {
        $expected = "\x1B" . '[30;42m Hooray ' . "\x1B" . '[m' . PHP_EOL;

        ob_start();
        $this->getResponse()->success('Hooray');
        $result = ob_get_clean();

        //echo $this->varExportString($result);
        $this->assertSame($expected, $result);
    }

    public function testText()
    {
        $expected = 'Neutral message' . PHP_EOL;

        ob_start();
        $this->getResponse()->addContent(new Text('Neutral message'));
        $result = ob_get_clean();

        //echo $this->varExportString($result);
        $this->assertSame($expected, $result);
    }

    public function testSubContent()
    {
        $expected = '   ' . 'Neutral message' . PHP_EOL;

        ob_start();
        $this->getResponse()->addContent(new SubContent(new Text('Neutral message')));
        $result = ob_get_clean();

        //echo $this->varExportString($result);
        $this->assertSame($expected, $result);
    }

}