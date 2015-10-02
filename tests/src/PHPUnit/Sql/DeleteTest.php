<?php


namespace YaoiTests\PHPUnit\Sql;
class DeleteTest extends \YaoiTests\PHPUnit\Sql\TestBase
{
    public function setUp()
    {
        try {
            \Yaoi\Database::getInstance('test_mysqli');
        } catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }


    public function testDelete()
    {
        $this->assertSame(
            'DELETE FROM table WHERE one = 1',
            (string)\Yaoi\Database::getInstance('test_mysqli')->delete('table')->where('one = ?', 1)
        );
    }
} 