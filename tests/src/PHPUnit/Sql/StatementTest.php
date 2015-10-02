<?php
namespace YaoiTests\PHPUnit\Sql;

use Yaoi;
use Yaoi\Database;
use Yaoi\Sql\Statement;


class StatementTest extends \YaoiTests\PHPUnit\Sql\TestBase
{
    public function testQueryClient()
    {
        $s = new Statement();
        $s->select()->from('table');
        $client = Database::getInstance('test_mysqli');
        $this->assertSame('SELECT * FROM table',
            $s->query($client)->skipAutoExecute()->build());
    }


    public function testToStringException()
    {
        $s = new Statement();
        $s->select()->from('1 AND ? AND ?', 2);
        $s->bindDatabase(Yaoi\Database::getInstance('test_mysqli'));
        $this->assertSame('/* ERROR: Redundant placeholder: "1 AND 2 AND ?" */', (string)$s);
    }

    public function testExpr()
    {
        $s = new Statement();
        $e = $s->expr('1 = 1');
        $this->assertSame('1 = 1', $e->build());
    }

    public function testEmptyTables()
    {
        $s = new Statement();
        $s->delete();
        $s->bindDatabase(Yaoi\Database::getInstance('test_mysqli'));
        $this->assertSame('DELETE', (string)$s);
    }

    public function testBuild()
    {
        $s = Yaoi\Database::getInstance('test_mysqli')->statement();
        $this->assertSame('', $s->build());
    }
}