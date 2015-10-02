<?php

namespace YaoiTests\PHPUnit\Sql;
use Yaoi;

class InsertTest extends \YaoiTests\PHPUnit\Sql\TestBase
{
    public function testSingleRow()
    {
        $statement = Yaoi\Database::getInstance('test_mysqli')->statement();

        $statement->insert('table')
            ->valuesRow(array('a' => 1, 'b' => 2))
            ->valuesRow(array('b' => 3, 'c' => 4));

        //var_dump($u);

        $this->assertSame('INSERT INTO table (`a`, `b`, `c`) VALUES (1, 2, DEFAULT), (DEFAULT, 3, 4)', (string)$statement);

    }

    public function testMultipleRows()
    {
        $i = Yaoi\Database::getInstance('test_mysqli')->insert('table2')->valuesRows(array(
            array('a' => 1, 'b' => 2),
            array('b' => 3, 'c' => 4),
            array('a' => 5, 'c' => 7)
        ));

        $this->assertSame('INSERT INTO table2 (`a`, `b`, `c`) VALUES (1, 2, DEFAULT), (DEFAULT, 3, 4), (5, DEFAULT, 7)', (string)$i);
    }

    public function testEmpty()
    {
        $this->assertSame('INSERT INTO table', (string)Yaoi\Database::getInstance('test_mysqli')->insert('table'));

        $this->assertSame('INSERT INTO table', (string)Yaoi\Database::getInstance('test_mysqli')->insert('table')->valuesRows(array()));

    }
} 