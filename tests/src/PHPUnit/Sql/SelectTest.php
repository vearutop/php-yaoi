<?php
namespace YaoiTests\PHPUnit\Sql;

use Yaoi;
use Yaoi\Database;
use Yaoi\Sql\SimpleExpression;
use Yaoi\Sql\Statement;

class SelectTest extends \YaoiTests\PHPUnit\Sql\TestBase
{

    public function testOmg()
    {
        $db = Yaoi\Database::getInstance('test_mysqli');
        $se1 = $db->select('test');

        $se = $db->select()
            ->from('? AS oo', $se1)
            ->from('? AS ll', $se1)
            ->from('test');

        $this->assertSame(
            'SELECT * FROM (SELECT * FROM test) AS oo, (SELECT * FROM test) AS ll, test',
            (string)$se
        );
    }

    public function testEx()
    {
        $quoter = Database::getInstance()->getDriver();

        $ex = new SimpleExpression('ololo = ? AND bbb = ?', 12, 13);
        $ex->andExpr('lala = :ow', array('ow' => 'OWWW'));
        $this->assertSame("ololo = 12 AND bbb = 13 AND lala = 'OWWW'", $ex->build($quoter));

        $ex = new SimpleExpression('ololo = ? AND bbb = ?', 12, 13);
        $ex->andExpr('lala = :ow', array('ow' => 'OWWW'))->andExpr('omnom');
        $e2 = new SimpleExpression('hidden');
        $ex->orExpr($e2);

        $this->assertSame("ololo = 12 AND bbb = 13 AND lala = 'OWWW' AND omnom OR hidden", $ex->build($quoter));

        $e2->disable();
        $this->assertSame("ololo = 12 AND bbb = 13 AND lala = 'OWWW' AND omnom", $ex->build($quoter));

    }

    public function testAs()
    {
        $quoter = Database::getInstance()->getDriver();

        $ex = new SimpleExpression('ololo = ? AND bbb = ?', 12, 13);
        $ex->andExpr('lala = :ow', array('ow' => 'OWWW'));
        $ex->asExpr('alias');
        $this->assertSame("(ololo = 12 AND bbb = 13 AND lala = 'OWWW') AS alias", $ex->build($quoter));
    }


    public function testWhere()
    {
        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->where(null);
        $this->assertSame('SELECT * FROM t1', (string)$sql);

        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->where('a = ?', 2);
        $this->assertSame('SELECT * FROM t1 WHERE a = 2', (string)$sql);

        $expr = Yaoi\Database::getInstance('test_mysqli')->expr('a = ?', 2);
        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->where($expr)
            ->where('c > 1');
        $this->assertSame('SELECT * FROM t1 WHERE a = 2 AND c > 1', (string)$sql);


    }

    public function testSelect()
    {
        $se = new Statement();
        $se->select('VERSION(), FROM_UNIXTIME(?)', 12321);
        $se->select('now()');
        $se->select(function () {
            $e = new SimpleExpression('a-b');
            $e->asExpr('wkhoooy');
            return $e;
        });

        $this->assertSame("SELECT VERSION(), FROM_UNIXTIME(12321), now(), (a-b) AS wkhoooy", $se->build(Database::getInstance()->getDriver()));

        $se
            ->from('myTable AS m')
            ->from('brbrbr');

        $se2 = Yaoi\Database::getInstance('test_mysqli')->select('users')
            ->where('id in (:ids)', array('ids' => array(1, 2, 3)));
        $this->assertSame('SELECT * FROM users WHERE id in (1, 2, 3)', (string)$se2);


        $se3 = Yaoi\Database::getInstance('test_mysqli')->select('orders')
            ->where('id in (?)', array(array(1, 2, 3)));
        $this->assertSame('SELECT * FROM orders WHERE id in (1, 2, 3)', (string)$se3);


        $se->from('? AS sss', $se2);
        $this->assertSame("SELECT VERSION(), FROM_UNIXTIME(12321), now(), (a-b) AS wkhoooy FROM myTable AS m, brbrbr, (SELECT * FROM users WHERE id in (1, 2, 3)) AS sss",
            $se->build(Database::getInstance()->getDriver()));
    }


    public function testQuery()
    {
        $this->assertSame('SELECT * FROM test1 WHERE id=1',
            (string)Yaoi\Database::getInstance('test_mysqli')->select('test1')
                ->where('id=?', 1)
                ->query()->skipAutoExecute()->build());
    }


    public function testOrder()
    {
        $this->assertSame('SELECT * FROM test1 WHERE id=1 ORDER BY id DESC, field BETWEEN 12 AND 13 ASC',
            (string)Yaoi\Database::getInstance('test_mysqli')->select('test1')
                ->where('id=?', 1)
                ->order('id DESC')
                ->order('field BETWEEN ? AND ? ASC', 12, 13)
                ->query()->skipAutoExecute()->build());


        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->order(null);
        $this->assertSame('SELECT * FROM t1', (string)$sql);

    }


    public function testOrder2()
    {
        $order = Yaoi\Database::getInstance('test_mysqli')->expr('field2 = ?', 'lol')->commaExpr('field3 DESC');
        $this->assertSame('SELECT * FROM test1 WHERE id=1 ORDER BY id DESC, field BETWEEN 12 AND 13 ASC, field2 = \'lol\', field3 DESC',
            (string)Yaoi\Database::getInstance('test_mysqli')->select('test1')
                ->where('id=?', 1)
                ->order('id DESC')
                ->order('field BETWEEN ? AND ? ASC', 12, 13)
                ->order($order)
                ->query()->skipAutoExecute()->build());

        $order->disable();
        $this->assertSame('SELECT * FROM test1 WHERE id=1 ORDER BY id DESC, field BETWEEN 12 AND 13 ASC',
            (string)Yaoi\Database::getInstance('test_mysqli')->select('test1')
                ->where('id=?', 1)
                ->order('id DESC')
                ->order('field BETWEEN ? AND ? ASC', 12, 13)
                ->order($order)
                ->query()->skipAutoExecute()->build());

    }


    public function testJoin()
    {
        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->leftJoin('t2 ON t1.id = t2.tid');
        $this->assertSame('SELECT * FROM t1 LEFT JOIN t2 ON t1.id = t2.tid', (string)$sql);

        $expected = 'SELECT * FROM test3 LEFT JOIN (select * from test2, test1 where test2.sourceId = test1.id AND test2.temperature > 0) AS tt ON test3.sourceId = tt.sourceId RIGHT JOIN (select * from test2, test1 where test2.sourceId = test1.id AND test2.temperature > 0) AS tt2 ON test2.sourceId = tt2.sourceId INNER JOIN test5 AS ss ON (field1 = 1 AND field2 = 2)';

        $derived = Yaoi\Database::getInstance('test_mysqli')->expr('select * from test2, test1 where test2.sourceId = test1.id AND test2.temperature > 0');
        $condition = Yaoi\Database::getInstance('test_mysqli')->expr('field1 = ? AND field2 = ?', 1, 2);
        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('test3')
            ->leftJoin('? AS tt ON test3.sourceId = tt.sourceId', $derived)
            ->rightJoin('? AS tt2 ON test2.sourceId = tt2.sourceId', $derived)
            ->innerJoin('test5 AS ss ON ?', $condition);
        $this->assertSame($expected, (string)$sql);

    }


    public function testLimit()
    {
        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->limit(5)
            ->offset(6);
        $this->assertSame('SELECT * FROM t1 LIMIT 5 OFFSET 6', (string)$sql);


        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->limit(5, 6);
        $this->assertSame('SELECT * FROM t1 LIMIT 5 OFFSET 6', (string)$sql);


        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->limit(null)
            ->offset(6);
        $this->assertSame('SELECT * FROM t1', (string)$sql);

    }


    public function testGroupBy()
    {
        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->groupBy(null);

        $this->assertSame('SELECT * FROM t1', (string)$sql);

        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->groupBy('name');
        $this->assertSame('SELECT * FROM t1 GROUP BY name', (string)$sql);

        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->groupBy('name')
            ->groupBy('age');
        $this->assertSame('SELECT * FROM t1 GROUP BY name, age', (string)$sql);
    }


    public function testHaving()
    {
        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->having(null);
        $this->assertSame('SELECT * FROM t1', (string)$sql);

        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->having('name = ?', 'Igor');
        $this->assertSame('SELECT * FROM t1 HAVING name = \'Igor\'', (string)$sql);

        $sql = Yaoi\Database::getInstance('test_mysqli')
            ->select('t1')
            ->having('name = 2')
            ->having('age > 3');
        $this->assertSame('SELECT * FROM t1 HAVING name = 2 AND age > 3', (string)$sql);
    }


    public function testEmptyColumns()
    {
        $cols = Yaoi\Database::getInstance('test_mysqli')->expr('1')->disable();
        $s = Yaoi\Database::getInstance('test_mysqli')->select('table')->select($cols);
        $this->assertSame('/* ERROR: Missing columns in SELECT statement */', (string)$s);
    }

}