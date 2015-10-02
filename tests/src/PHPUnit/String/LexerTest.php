<?php

namespace YaoiTests\PHPUnit\String;

use Yaoi\String\Lexer;
use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\String\Lexer\Renderer;

class LexerTest extends TestCase
{
    public function testTokenize() {
        $t2 = new Lexer\Parser();
        $t2->addQuote("'", "'", array("''" => "'"));
        $t2->addQuote('/*', '*/');
        $t2->addLineStopper('-- ');
        $t2->addLineStopper('#');



        $string = <<<EOD
Hello 'World' of 'John O''Connor'
Welcome! -- line prop 'not parsed' here
Peace...
/* some inline comment */ yup # and a line stopper /* not parsed */
EOD;


        $parsed = $t2->tokenize($string);
        $this->assertSame('Hello :B0: of :B1:
Welcome! :B2:
Peace...
:B3: yup :B4:', Lexer\Renderer::create()->getExpression($parsed)->getStatement());

        $this->assertSame('Hello :B0: of :B1:
Welcome! :B2:
Peace...
/* some inline comment */ yup ', Lexer\Renderer::create()->keep('/*')->strip('#')
            ->getExpression($parsed)->getStatement());
    }


    public function testTokenizeTwo() {
        $string = <<<EOD
SELECT 'one', `ta``ble`.* # hello there!
FROM `ta``ble` -- and here
WHERE a = '1 AND b = 0'
EOD;

        $tokenizer = new Lexer\Parser();
        $tokenizer
            ->addLineStopper('#')
            ->addLineStopper('-- ')
            ->addQuote('`', '`', array('``' => '`'))
            ->addQuote("'","'", array("''" => "'"));


        $this->assertSame('SELECT :B0:, :B1:.* :B2:
FROM :B3: :B4:
WHERE a = :B5:', Lexer\Renderer::create()->getExpression($tokenizer->tokenize($string))->getStatement());

        $this->assertStringEqualsSpaceless('SELECT :B0:, `ta``ble`.*
FROM `ta``ble`
WHERE a = :B1:', Lexer\Renderer::create()->keep('`')->strip('#', '-- ')
            ->getExpression($tokenizer->tokenize($string))->getStatement());

        $expression = Lexer\Renderer::create()->getExpression($tokenizer->tokenize($string));
        $this->assertSame($string, $expression->build(new Lexer\Quoter()));
    }

    public function testThree() {
        $string = <<<EOD
line 'line' line
EOD;
        $tokenizer = new Lexer\Parser();
        $tokenizer->addQuote("'", "'");
        $this->assertSame('line :B0: line', Lexer\Renderer::create()
            ->getExpression($tokenizer->tokenize($string))->getStatement());
    }


    /**
     * @expectedException \Yaoi\String\Exception
     * @expectedExceptionCode \Yaoi\String\Exception::MALFORMED
     * @throws \Yaoi\String\Exception
     */
    public function testMalformed() {
        $string = <<<EOD
line 'line line
EOD;
        $tokenizer = new Lexer\Parser();
        $tokenizer->addQuote("'", "'");
        $this->assertSame(array('line ', array('line', "'"), ' line'), $tokenizer->tokenize($string));
    }


    public function testElementary() {
        $tokenizer = new Lexer\Parser();
        $tokenizer->addQuote("q", "q", array("eq" => "q", 'nnq' => 'q2'));

        $string = 'q eqnnq q';
        $this->assertSame(":B0:", Lexer\Renderer::create()->getExpression($tokenizer->tokenize($string))->getStatement());
        $this->assertSame("q eqnnq q", Lexer\Renderer::create()->keep('q')->getExpression($tokenizer->tokenize($string))->getStatement());

        /** @var Lexer\Token[] $binds */
        $binds = Lexer\Renderer::create()->getExpression($tokenizer->tokenize($string))->getBinds();
        $this->assertSame(' eqnnq ', $binds[':B0:']->escapedContent);
        $this->assertSame(' qq2 ', $binds[':B0:']->unEscapedContent);
    }


    public function testBracketsPre() {
        $tokenizer = new Lexer\Parser();
        $tokenizer->addBracket('(', ')');
        $tokenizer->addBracket('[', ']');
        $tokenizer->addBracket('{', '}');
        $tokenizer->addQuote('\'','\'', array('\\\'' => '\''));
        $tokenizer->addQuote('"', '"', array('\\"' => '"'));

        $string = <<<'PHP'
if (1 2 3,'''', 'aa') { $a } ;
PHP;
        $this->assertSame('if :B0: :B1: ;', Renderer::create()
            ->getExpression($tokenizer->tokenize($string))->getStatement());
        $this->assertSame('if :B0: { $a } ;', Renderer::create()->keep('{')
            ->getExpression($tokenizer->tokenize($string))->getStatement());
        $this->assertSame("if (1 2 3,'''', 'aa') :B0: ;", Renderer::create()->keep('(', "'")
            ->getExpression($tokenizer->tokenize($string))->getStatement());
        $this->assertSame("if (1 2 3,:B0::B1:, :B2:) :B3: ;", Renderer::create()->keep('(')
            ->getExpression($tokenizer->tokenize($string))->getStatement());
    }

    public function testBrackets() {
        $tokenizer = new Lexer\Parser();
        $tokenizer->addBracket('(', ')');
        $tokenizer->addBracket('[', ']');
        $tokenizer->addBracket('{', '}');
        $tokenizer->addQuote('\'','\'', array('\\\'' => '\''));
        $tokenizer->addQuote('"', '"', array('\\"' => '"'));

        $string = <<<'PHP'
if ('a' === "'a'") {
    array('c') == ['c'];
    $fff = 'O\'Lolo';
};
PHP;
        $this->assertSame('if :B0: :B1:;', Renderer::create()
            ->getExpression($tokenizer->tokenize($string))->getStatement());

        $this->assertSame('if (:B0: === :B1:) :B2:;', Renderer::create()->keep('(')
            ->getExpression($tokenizer->tokenize($string))->getStatement());
    }


    public function testOneLetter() {
        $string = "'a', 'b', 'c'";
        $tokenizer = new Lexer\Parser();
        $tokenizer->addQuote("'", "'");
        $this->assertSame(':B0:, :B1:, :B2:', Renderer::create()
            ->getExpression($tokenizer->tokenize($string))->getStatement());
    }

    public function testCustomBinds() {
        $string = "'a', 'b', 'c'";
        $tokenizer = new Lexer\Parser();
        $tokenizer->addQuote("'", "'");
        $this->assertSame('~0~, ~1~, ~2~',
            Renderer::create()->setBindKey('~', '~')
                ->getExpression($tokenizer->tokenize($string))->getStatement());

        $this->assertSame('0, 1, 2',
            Renderer::create()->setBindKey('', '')
                ->getExpression($tokenizer->tokenize($string))->getStatement());
    }

    public function testSeparator() {
        $string = "'a', 'b,b,b',, (1,2,3), 'c,d'";
        $tokenizer = new Lexer\Parser();
        $tokenizer->addQuote("'", "'");
        $tokenizer->addBracket('(', ')');
        $tokenizer->addDelimiter(',');
        $parsed = $tokenizer->tokenize($string);
        $items = $parsed->split(',');
        $replaced = '';
        $renderer = Renderer::create()->keep("'");
        foreach ($items as $item) {
            $replaced .= $renderer->getExpression($item)->getStatement() . ';';
        }

        $this->assertSame("'a'; 'b,b,b';; :B0:; 'c,d';", $replaced);
    }


    public function testKeepBoundaries() {
        $string = '"a", "b", ("c", "d),("), e';
        $tokenizer = new Lexer\Parser();
        $tokenizer->addQuote('"', '"');
        $tokenizer->addDelimiter(',');
        $tokenizer->addBracket('(', ')');

        $parsed = $tokenizer->tokenize($string);
        $renderer = Renderer::create()->keepBoundaries('(', '"');
        $this->assertSame('":B0:", ":B1:", (:B2:), e', $renderer->getExpression($parsed)->getStatement());
    }
}