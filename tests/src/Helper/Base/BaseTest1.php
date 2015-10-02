<?php
namespace YaoiTests\Helper\Base;

use Yaoi\BaseClass;

class BaseTest1 extends BaseClass
{
    public $amount;

    public function __construct($var1, $var2, $var3, $var4, $var5)
    {
        $this->amount = $var1 + $var2 + $var3 + $var4 + $var5;
    }
}