<?php

namespace YaoiTests\PHPUnit\Database;

use Yaoi\Database;
use Yaoi\Database\Driver\MockProxy;
use Yaoi\Mock;
use Yaoi\Storage;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\App;

class MockTest extends TestCase
{
    public function setUp()
    {
        \YaoiTests\Helper\Database\CheckAvailable::getMysqli();
    }


    /**
     * @var PhpVar
     */
    public static $testOne;

    public function testBase()
    {
        $db = App::database('test_mysqli');
        $storage = new Storage('php-var://dummy/?staticPropertyRef=MockTest::$testOne');
        $mockSet = new Mock($storage, Mock::MODE_CAPTURE);
        $db->mock($mockSet);

        $result = array();
        $result['now'] = $db->query("SELECT NOW() AS now")->rowsAffectedIn($result['aff'])->fetchRow('now');
        $result['ut'] = $db->query("SELECT UNIX_TIMESTAMP() AS ut")->fetchRow('ut');
        $result['rnd'] = $db->query("SELECT RAND() AS rnd")->fetchRow('rnd');
        $result['rnd2'] = $db->query("SELECT RAND() AS rnd")->fetchRow('rnd');
        $result['rnd3'] = $db->query("SELECT RAND() AS rnd")->fetchRow('rnd');
        $result['rows'] = $db->query("SELECT 1 AS ok UNION ALL SELECT 2 UNION ALL SELECT 3")
            ->rowsAffectedIn($result['aff2'])->fetchAll();
        $expected = $result;

        //print_r($result);

        $mockSet = new Mock($storage, Mock::MODE_PLAY);
        $db->mock($mockSet);

        $result = array();
        $result['now'] = $db->query("SELECT NOW() AS now")->rowsAffectedIn($result['aff'])->fetchRow('now');
        $result['ut'] = $db->query("SELECT UNIX_TIMESTAMP() AS ut")->fetchRow('ut');
        $result['rnd'] = $db->query("SELECT RAND() AS rnd")->fetchRow('rnd');
        $result['rnd2'] = $db->query("SELECT RAND() AS rnd")->fetchRow('rnd');
        $result['rnd3'] = $db->query("SELECT RAND() AS rnd")->fetchRow('rnd');
        $result['rows'] = $db->query("SELECT 1 AS ok UNION ALL SELECT 2 UNION ALL SELECT 3")
            ->rowsAffectedIn($result['aff2'])->fetchAll();

        $this->assertSame($expected, $result);

        $db->mock();
    }


    public function testMockSwitch()
    {
        $db = Database::getInstance('test_mysqli');

        $db->mock();
        $this->assertFalse($db->getDriver() instanceof MockProxy);

        $db->mock(new Mock(new PhpVar, Mock::MODE_PLAY));
        $this->assertTrue($db->getDriver() instanceof MockProxy);

        $db->mock(new Mock(new PhpVar, Mock::MODE_CAPTURE));
        $this->assertTrue($db->getDriver() instanceof MockProxy);

        $db->mock();
        $this->assertFalse($db->getDriver() instanceof MockProxy);

        $db->mock(new Mock(new PhpVar, Mock::MODE_CAPTURE));
        $this->assertTrue($db->getDriver() instanceof MockProxy);

        $db->mock();
        $this->assertFalse($db->getDriver() instanceof MockProxy);
    }

}