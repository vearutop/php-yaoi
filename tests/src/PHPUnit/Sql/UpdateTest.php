<?php
namespace YaoiTests\PHPUnit\Sql;

use Yaoi;
use Yaoi\Sql\DefaultValue;

class UpdateTest extends \YaoiTests\PHPUnit\Sql\TestBase
{
    public function testUpdate()
    {
        $u = Yaoi\Database::getInstance('test_mysqli')->statement();

        $u->update('table1')->update('table2');
        $u->set('a = 1, b = 2');
        $this->assertSame('UPDATE table1, table2 SET a = 1, b = 2', (string)$u);
    }


    public function testSetArray()
    {
        $u = Yaoi\Database::getInstance('test_mysqli')->statement();

        $u
            ->update('table1')
            ->set(array('a' => '1', 'b' => 2, 'c' => new DefaultValue()));
        $this->assertSame("UPDATE table1 SET `a` = '1', `b` = 2, `c` = DEFAULT", (string)$u);
    }

    public function testComplex()
    {
        $u = Yaoi\Database::getInstance('test_mysqli')->statement();

        $u->update('table1');
        $u->set(null);
        $u->set(array('a' => '1', 'b' => 2, 'c' => new DefaultValue()));
        $u->set(array('a' => '1', 'b' => 2, 'c' => new DefaultValue()), 't2');
        $u->where('t2.a = 1 AND t2.b = ? AND t2.c = ?', 5, 6);
        $u->leftJoin('table2 AS t2 ON t2.a = table1.a');
        $u->update('table2');

        $this->assertSame("UPDATE table1, table2 LEFT JOIN table2 AS t2 ON t2.a = table1.a SET `a` = '1', `b` = 2, `c` = DEFAULT, `t2`.`a` = '1', `t2`.`b` = 2, `t2`.`c` = DEFAULT WHERE t2.a = 1 AND t2.b = 5 AND t2.c = 6", (string)$u);
    }

    public function testEmptySet()
    {
        // TODO throw exception on wrong statements?

        $u = Yaoi\Database::getInstance('test_mysqli')->update('table');

        $this->assertSame('UPDATE table', (string)$u);
    }

}