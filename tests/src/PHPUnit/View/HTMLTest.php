<?php
namespace YaoiTests\PHPUnit\View;

use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\View\HTML\Element;
use Yaoi\View\HTML\Input;
use Yaoi\View\HTML\Select;


class HTMLTest extends TestCase
{
    public function testEscape()
    {
        $this->assertSame('1&lt;2', Element::escapeContent('1<2'));
        $this->assertSame('&quot;Peter O&#39;tool&quot;', Element::escapeValue('"Peter O\'tool"'));
        $this->assertSame('123sdf', Element::escapeContent('123sdf'));
        $this->assertSame('123sdf', Element::escapeValue('123sdf'));
    }

    public function testInput()
    {
        $this->assertSame(
            '<input name="test_name" value="test_value" />',

            (string)Input::create()
                ->setName('test_name')
                ->setValue('test_value')
        );

        $select = Select::create()
            ->setName('test_name')
            ->setOptions(array('one' => '1', 'two' => '2', 'three' => 3))
            ->setValue('test_value');

        $this->assertSame(
            '<select name="test_name"><option value="one">1</option><option value="two">2</option><option value="three">3</option></select>',

            (string)$select
        );

        // re-render
        $select->setName('test2_name');
        $this->assertSame(
            '<select name="test2_name"><option value="one">1</option><option value="two">2</option><option value="three">3</option></select>',
            (string)$select
        );


        $this->assertSame(
            '<select name="test_name"><option value="one">1</option><option value="two">2</option><option value="three" selected="selected">3</option></select>',

            (string)Select::create()
                ->setName('test_name')
                ->setOptions(array('one' => '1', 'two' => '2', 'three' => 3))
                ->setValue('three')
        );

        $form = array(
            't1' => 'one',
            't2' => 'two',
        );

        $this->assertSame(
            '<select name="t1"><option value="one" selected="selected">1</option><option value="two">2</option><option value="three">3</option></select>',

            (string)Select::create()
                ->setName('t1')
                ->setOptions(array('one' => '1', 'two' => '2', 'three' => 3))
                ->fillValue($form)
        );

        $this->assertSame(
            '<input name="t2" value="two" />',

            (string)Input::create()
                ->setName('t2')
                ->fillValue($form)
        );

        $_REQUEST = array(
            't1' => 'one1',
            't2' => 'two2',
        );


        $this->assertSame(
            '<input name="t2" value="two2" />',

            (string)Input::create()
                ->setName('t2')
                ->fillValue()
        );

    }


    public function testRepeat()
    {
        $h = new Select();
        $this->assertSame('<select></select>', (string)$h);

        $h->setOptions(array(1, 2, 3));
        $this->assertSame('<select><option value="0">1</option><option value="1">2</option><option value="2">3</option></select>', (string)$h);
    }

    public function testUnsetAttribute()
    {
        $h = new Select();
        $h->setAttribute('test', 'test');
        $this->assertSame('<select test="test"></select>', (string)$h);

        $h->setAttribute('test');
        $this->assertSame('<select></select>', (string)$h);

    }

    public function testAttributes()
    {
        $h = new Select();
        $h->onClick('alert("1")');
        $this->assertSame('<select onclick="alert(&quot;1&quot;)"></select>', (string)$h);
    }

    public function testIsEmpty()
    {
        $h = new Select();
        $this->assertSame(true, $h->isEmpty());

        $h->setOptions(array(1, 2, 3));
        $this->assertSame(false, $h->isEmpty());

        $h2 = new Element();
        $this->assertSame(true, $h2->isEmpty());

        $h2->setContent($h);
        $this->assertSame(false, $h2->isEmpty());

        $this->assertSame('<select><option value="0">1</option><option value="1">2</option><option value="2">3</option></select>',
            (string)$h2);

    }


    public function testClasses()
    {
        $h = new Select();
        $h->addClass('test')->addClass('test2');
        $this->assertSame('<select class="test test2"></select>', (string)$h);


        $h->removeClass('test');
        $this->assertSame('<select class="test2"></select>', (string)$h);
    }


    /**
     * @expectedException     \Yaoi\View\Exception
     * @expectedExceptionCode \Yaoi\View\Exception::WRONG_DATA_TYPE
     */
    public function testSelectWrongOptions()
    {
        Select::create()
            ->setName('t1')
            ->setOptions('string');
    }
}