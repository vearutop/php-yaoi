<?php
namespace YaoiTests\PHPUnit\Sql;

use Yaoi\Database;
use Yaoi\Sql\SimpleExpression;


class ExpressionTest extends \YaoiTests\PHPUnit\Sql\TestBase
{

    public function testNullBind()
    {
        $this->assertSame('NULL', (string)Database::getInstance('test_mysqli')->expr('?', null));
    }

    public function testExpression()
    {
        $s = new SimpleExpression('test ? ? ?', 1, 2, 3);
        $driver = Database::getInstance('test_mysqli')->getDriver();

        $this->assertSame('test 1 2 3', $s->build($driver));
    }


    public function testEmpty()
    {
        $s = new SimpleExpression();
        $this->assertSame(true, $s->isEmpty());
        $this->assertSame('', $s->build());
    }

    /**
     * @expectedException     \Yaoi\Sql\Exception
     * @expectedExceptionCode \Yaoi\Sql\Exception::STATEMENT_REQUIRED
     */
    public function testCreateFromArgumentsStatementRequired()
    {
        SimpleExpression::createFromFuncArguments(array());
    }


    public function testCreateFromArgumentsProxy()
    {
        $ex = new SimpleExpression('test');
        $this->assertSame($ex, SimpleExpression::createFromFuncArguments(array($ex)));
    }

    public function testCreateFromArgumentsClosure()
    {
        $ex = new SimpleExpression('test');
        $this->assertSame($ex, SimpleExpression::createFromFuncArguments(array(
            function () use ($ex) {
                return $ex;
            }
        )));
    }


    /**
     * @expectedException     \Yaoi\Sql\Exception
     * @expectedExceptionCode \Yaoi\Sql\Exception::CLOSURE_MISTYPE
     */
    public function testCreateFromArgumentsBadClosure()
    {
        SimpleExpression::createFromFuncArguments(array(
            function () {
                return 'not a Sql_Expression instance';
            }
        ));
    }


    public function testXor()
    {
        $ex = new SimpleExpression('1');
        $ex->xorExpr('2');
        $this->assertSame('1 XOR 2', $ex->build());
    }


    /**
     * @expectedException     \Yaoi\Database\Exception
     * @expectedExceptionCode \Yaoi\Database\Exception::PLACEHOLDER_NOT_FOUND
     */
    public function testPlaceholderNotFound()
    {
        $ex = new SimpleExpression('1 = 1', 1);
        $ex->build(Database::getInstance()->getDriver());
    }

    /**
     * @expectedException     \Yaoi\Database\Exception
     * @expectedExceptionCode \Yaoi\Database\Exception::PLACEHOLDER_REDUNDANT
     */
    public function testPlaceholderRedundant()
    {
        $ex = new SimpleExpression('1 = ? AND 2 = ?', 1);
        $ex->build(Database::getInstance()->getDriver());
    }


    public function testEnable()
    {
        $ex = new SimpleExpression('1');
        $or = new SimpleExpression('2');

        $ex->orExpr($or);
        $this->assertSame('1 OR 2', $ex->build());

        $or->disable();
        $this->assertSame('1', $ex->build());

        $or->enable();
        $this->assertSame('1 OR 2', $ex->build());
    }


}