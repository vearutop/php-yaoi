<?php

namespace YaoiTests\PHPUnit\Database\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\String\Parser;
use Yaoi\Test\PHPUnit\TestCase;

class ColumnTest extends TestCase
{
    /**
     * @see Yaoi\Database\Definition\Column::castField
     */
    public function testCastField() {
        $this->assertSame(1, Column::castField('1', Column::INTEGER + Column::AUTO_ID));
        $this->assertSame('1', Column::castField(1, Column::STRING + Column::NOT_NULL));
        $this->assertSame(1.0, Column::castField('1', Column::FLOAT));
    }

    /**
     * @see Yaoi\Database\Definition\Column::castField
     */
    public function testObjectToString() {
        $this->assertSame(
            1,
            Column::castField(
                Parser::create('<i>1.0</i>')->inner('<i>','</i>'), // Parser object with value of '1.0'
                Column::INTEGER
            )
        );
    }


    /**
     * @see Column::setFlag
     */
    public function testSetFlag() {
        $column = new Column(Column::INTEGER);
        $this->assertSame(Column::INTEGER, $column->flags);

        $column->setFlag(Column::NOT_NULL);
        $this->assertSame(Column::INTEGER + Column::NOT_NULL, $column->flags);

        // no change on second set
        $column->setFlag(Column::NOT_NULL, true);
        $this->assertSame(Column::INTEGER + Column::NOT_NULL, $column->flags);

        $column->setFlag(Column::NOT_NULL, false);
        $this->assertSame(Column::INTEGER, $column->flags);

        // no change on second set
        $column->setFlag(Column::NOT_NULL, false);
        $this->assertSame(Column::INTEGER, $column->flags);


        $column->setFlag(Column::NOT_NULL);
        $column->setFlag(Column::AUTO_ID);
        $this->assertSame(Column::INTEGER + Column::NOT_NULL + Column::AUTO_ID, $column->flags);

    }


    /**
     * @see Column::setStringLength
     * @see Column::setStringFixed
     */
    public function testSetStringLength() {
        $column = new Column(Column::STRING);

        $column->setStringLength(10, true);
        $this->assertSame(10, $column->stringLength);
        $this->assertSame(true, $column->stringFixed);
    }

}