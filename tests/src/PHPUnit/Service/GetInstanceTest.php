<?php
namespace YaoiTests\PHPUnit\Service;

use Yaoi\Service;
use YaoiTests\Helper\Service\Another;
use YaoiTests\Helper\Service\BasicExposed;
use YaoiTests\Helper\Service\DefaultPrimary;
use YaoiTests\Helper\Service\NoSettings;

class GetInstanceTest extends \Yaoi\Test\PHPUnit\TestCase
{
    /**
     * If you specify $identifier of wrong type you will get `Service\Exception`
     * @see Service::getInstance
     *
     * @expectedExceptionCode \Yaoi\Service\Exception::INVALID_ARGUMENT
     * @expectedException \Yaoi\Service\Exception
     */
    public function testSettingsInvalidArgument() {
        BasicExposed::getInstance(new \stdClass());
    }


    /**
     * Primary instance is returned by default, null $identifier is also a default
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testPrimary() {
        BasicExposed::register('test://');
        $this->assertSame(BasicExposed::getInstance(), BasicExposed::getInstance(Service::PRIMARY));
        $this->assertSame(BasicExposed::getInstance(null), BasicExposed::getInstance(Service::PRIMARY));

    }

    /**
     * If fallback is set, it will be returned for unknown instances
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testFallback() {
        Another::register('test://', Service::FALLBACK);
        Another::register('test2://', 'known');

        $this->assertSame(Another::getInstance(), Another::getInstance('unknown'));
        $this->assertNotSame(Another::getInstance('unknown'), Another::getInstance('known'));
    }

    /**
     * If no fallback is set exception will be thrown
     *
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_FALLBACK
     * @expectedException \Yaoi\Service\Exception
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testMissingFallback() {
        BasicExposed::getInstance('unknown');
    }


    /**
     * Any object of class Service or its descendant can be passed as argument and returned unchanged
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testServicePassThrough() {
        $test1 = new Another();

        $this->assertSame($test1, BasicExposed::getInstance($test1));
        $this->assertSame($test1, Another::getInstance($test1));
    }


    /**
     * You can pass Settings object to create Service instance
     *
     * @throws Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testSettingsGetInstance() {
        $settings = BasicExposed::createSettings();

        $this->assertSame($settings, BasicExposed::getInstance($settings)->getSettings());
    }


    /**
     * Primary config can be set up in class definition
     *
     * @throws Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testDefaultPrimary() {
        $this->assertInstanceOf(DefaultPrimary::className(), DefaultPrimary::getInstance());
    }


    /**
     * If Closure is passed, its result is being processed
     *
     * @throws Service\Exception
     * @see \Yaoi\Service::getInstance as $example
     */
    public function testClosureInstance() {
        $instance = new BasicExposed();
        $settings = BasicExposed::createSettings();

        $this->assertSame(
            $instance,
            BasicExposed::getInstance(
                function () use ($instance) {
                    return $instance;
                }
            )
        );


        $this->assertSame(
            $settings,
            BasicExposed::getInstance(
                function () use ($settings) {
                    return $settings;
                }
            )->getSettings()
        );
    }


    /**
     * You can not use string settings in getInstance because it is ambiguous with identifiers
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_FALLBACK
     * @expectedException \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testByStringDsn() {
        BasicExposed::getInstance('test://test/?');
    }


    /**
     * You can not use string settings in getInstance because it is ambiguous with identifiers
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_FALLBACK
     * @expectedException \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testByStringDsnInClosure() {
        BasicExposed::getInstance(function(){return 'test://test/?';});
    }




}