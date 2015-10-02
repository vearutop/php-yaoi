<?php
namespace YaoiTests\Helper\Mappable;

use Yaoi\Test\PHPUnit\TestCase;


class MappableTest1 extends \Yaoi\Mappable\Base
{
    public $one;
    public $two;
    public $three;

    public $nonMapped;
    protected static $mappedProperties = array(
        'three',
        'two',
        'one',
    );
}