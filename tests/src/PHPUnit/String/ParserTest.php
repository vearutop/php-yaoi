<?php
namespace YaoiTests\PHPUnit\String;

use Yaoi\String\Parser;
use Yaoi\Test\PHPUnit\TestCase;


class ParserTest extends TestCase
{
    public function testInner()
    {
        $s = new Parser('bla bla bla <strong class="tough">it\'s ok to be <em>gay</em></strong> bla bla');
        $d = $s->inner('<strong', '</strong>');
        $this->assertSame('tough', (string)$d->inner('class="', '"'));
        $this->assertSame('gay', (string)$d->inner('<em>', '</em>'));

        $this->assertSame('lala', (string)Parser::create('lala')->inner());
        $this->assertSame('12', (string)Parser::create('1234')->inner(null, 3));
        $this->assertSame('34', (string)Parser::create('1234')->inner(2, null));
        $this->assertSame('', (string)Parser::create('lala')->inner('la', 'la'));
        $this->assertSame('', (string)Parser::create('lala')->inner('ne', 'la'));
        $this->assertSame('', (string)Parser::create('lala')->inner('la', 'ne'));
    }

    public function testNullString()
    {
        $p = Parser::create();
        $this->assertSame($p, $p->inner('la', 'la'));
    }

    public function testResetPosition()
    {
        $s = Parser::create('<omg thats="the" funky="shit" />');
        $this->assertSame('shit', (string)$s->inner('funky="', '"'));
        $this->assertSame('the', (string)$s->setOffset(0)->inner('thats="', '"'));
    }


    public function testOffset()
    {
        $s = Parser::create('[1] [2] [3] [4]');
        $this->assertSame('1', (string)$s->inner('[', ']'));
        $this->assertSame('2', (string)$s->inner('[', ']'));
        $this->assertSame(7, $s->getOffset());
        $this->assertSame('1', (string)$s->setOffset(0)->inner('[', ']'));
        $this->assertSame('3', (string)$s->setOffset(6)->inner('[', ']'));
    }

    public function testIterator()
    {
        //return;
        $sp = Parser::create('<tr><td>0</td><td>1</td><td>2</td></tr>');
        $sp->inner('<', '>');
        $iterator = $sp->innerAll('<td>', '</td>');
        //var_dump($iterator);

        foreach ($iterator as $i => $r) {
            //var_dump($r);
            $this->assertSame((string)$i, (string)$r);
        }

        // testing rewind
        foreach ($iterator as $i => $r) {
            $this->assertSame((string)$i, (string)$r);
        }

    }


    public function testRest()
    {
        $s = new Parser('<a:b>');
        $s2 = $s->inner('<', '>');

        $a = (string)$s2->inner(null, ':');
        $b = (string)$s2->inner();
        $this->assertSame('a', $a);
        $this->assertSame('b', $b);

    }

}