<?php
namespace YaoiTests\PHPUnit\Sql;

use Yaoi;
use Yaoi\Sql\Symbol;

class UnionTest extends \YaoiTests\PHPUnit\Sql\TestBase
{
    public function testUnion()
    {
        $result = (string)Yaoi\Database::getInstance('test_mysqli')->select('table1')->where('one = ?', 1)->groupBy('field')
            ->union("SELECT * FROM ?", new Symbol('table2'))
            ->unionAll(Yaoi\Database::getInstance('test_mysqli')->select('tt')->where('filtered = ?', 1));

        $this->assertSame('SELECT * FROM table1 WHERE one = 1 GROUP BY field UNION SELECT * FROM `table2` UNION ALL SELECT * FROM tt WHERE filtered = 1', $result);
    }
}