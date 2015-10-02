<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 7/29/15
 * Time: 11:55
 */

namespace YaoiTests\PHPUnit\Sql;


use Yaoi\Sql\SimpleExpression;
use Yaoi\String\Quoter\Raw;
use Yaoi\Test\PHPUnit\TestCase;

class Test extends TestCase
{

    /**
     * Appends expression without adding extra space
     *
     * @throws \Yaoi\Database\Exception
     * @see Expression::appendExpr
     */
    public function testAppend() {
        $this->assertSame(
            'start-middle-end',
            SimpleExpression::create('start')->appendExpr('-mid?l?', 'd', 'e')->appendExpr('-e?d', 'n')->build(new Raw())
        );
    }


    public function testQuote() {
        $this->assertSame('m1ddle', SimpleExpression::create('m?ddle', 1)->build(new Raw()));
    }

}