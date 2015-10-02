<?php

namespace YaoiTests\PHPUnit\Sql;
use Yaoi;

class JoinTest extends \YaoiTests\PHPUnit\Sql\TestBase
{
    public function testInnerJoin()
    {
        $s = Yaoi\Database::getInstance('test_mysqli')->statement();
        $s->select('*');
        $s->from('table1 AS t1');
        $s->innerJoin('table2 as t2 ON t1.f1 = t2.f1');
        $this->assertSame('SELECT * FROM table1 AS t1 INNER JOIN table2 as t2 ON t1.f1 = t2.f1',
            (string)$s);

    }

    public function testLeftJoin()
    {
        $s = Yaoi\Database::getInstance('test_mysqli')->statement();
        $s->select('*');
        $s->from('table1 AS t1');
        $s->leftJoin('table2 as t2 ON t1.f1 = t2.f1');
        $this->assertSame('SELECT * FROM table1 AS t1 LEFT JOIN table2 as t2 ON t1.f1 = t2.f1',
            (string)$s);

    }

    public function testRightJoin()
    {
        $s = Yaoi\Database::getInstance('test_mysqli')->statement();
        $s->select('*');
        $s->from('table1 AS t1');
        $s->rightJoin('table2 as t2 ON t1.f1 = t2.f1');
        $this->assertSame('SELECT * FROM table1 AS t1 RIGHT JOIN table2 as t2 ON t1.f1 = t2.f1',
            (string)$s);

    }

    public function testComplexJoin()
    {
        $s = Yaoi\Database::getInstance('test_mysqli')->statement();
        $s->select('*');
        $s->from('table1 AS t1');
        $s->leftJoin('table2 as t2 ON t1.f1 = t2.f1');
        $s->rightJoin('table3 as t3 ON t1.f1 = t3.f1');
        $s->innerJoin('table4 as t4 ON t1.f1 = t4.f1');
        $s->leftJoin('table5 as t5 ON t1.f1 = t5.f1');
        $this->assertSame('SELECT * FROM table1 AS t1 LEFT JOIN table2 as t2 ON t1.f1 = t2.f1 RIGHT JOIN table3 as t3 ON t1.f1 = t3.f1 INNER JOIN table4 as t4 ON t1.f1 = t4.f1 LEFT JOIN table5 as t5 ON t1.f1 = t5.f1',
            (string)$s);

    }


}