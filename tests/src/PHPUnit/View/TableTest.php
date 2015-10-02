<?php
namespace YaoiTests\PHPUnit\View;

use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\View\Table\HTML;


class TableTest extends TestCase
{
    public function testTable()
    {
        $rows = array();
        $rows [] = array('one' => 1, 'two' => 2);
        $rows [] = array('one' => 3, 'two' => null);
        $rows [] = array('one' => '<b>b</b>', 'two' => 2);
        $rows [] = array('one' => 1, 'two' => 'asd');

        ob_start();
        HTML::create($rows)->setId('my-id')->addClass('fuck')->render();
        $r = ob_get_contents();
        ob_end_clean();

        $e = '"<table id=\"my-id\" class=\"fuck\"><tr><th>one<\/th><th>two<\/th><\/tr>\n<tr><td>1<\/td><td>2<\/td><\/tr>\n<tr><td>3<\/td><td>NULL<\/td><\/tr>\n<tr><td><b>b<\/b><\/td><td>2<\/td><\/tr>\n<tr><td>1<\/td><td>asd<\/td><\/tr>\n<\/table>"';
        $this->assertSame($e, json_encode($r));

    }
}