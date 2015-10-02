<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 29.09.2015
 * Time: 15:50
 */
namespace YaoiTests\PHPUnit\Service;

use Yaoi\Storage;
use YaoiTests\Helper\Service\Yaoi\Storage\Driver\DriverClass;

class ServiceDriverTest extends \Yaoi\Test\PHPUnit\TestCase
{
    /**
     * You can use drivers from custom namespace
     *
     * @see Service::getDriver
     * @deprecated?
     */
    public function testCustomDriverDsn()
    {
        $this->markTestSkipped();
        $dsn = 'yaoi-tests.helper.custom-vendor.driver-class://user:pass@host:1234/path1/path2?param1=1&param2=2';
        $storage = new Storage($dsn);

        $this->assertTrue($storage->getDriver() instanceof DriverClass);
    }

    /**
     * You can specify any class for driver explicitly with `driverClassName` property of your settings
     *
     * @see Settings::driverClassName
     * @example
     */
    public function testCustomDriverClassName()
    {
        $settings = Storage::createSettings();
        $settings->driverClassName = DriverClass::className();

        $storage = new Storage($settings);
        $this->assertTrue($storage->getDriver() instanceof DriverClass);
    }

    /**
     * For setting standard drivers you can use DSN schema field
     *
     * @see Service::getDriver
     */
    public function testDsn()
    {
        $dsn = 'void://user:pass@host:1234/path1/path2?param1=1&param2=2';
        $storage = new Storage($dsn);

        $this->assertTrue($storage->getDriver() instanceof \Yaoi\Storage\Driver\Void);
    }

    /**
     * If you are trying to set non existent driver you will get an Exception
     *
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_DRIVER
     * @expectedException \Yaoi\Service\Exception
     */
    public function testNonExistent()
    {
        $storage = new Storage('non-existent');
        $storage->getDriver();
    }


    public function testForceDriver()
    {

    }
}