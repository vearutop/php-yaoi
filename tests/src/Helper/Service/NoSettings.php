<?php
namespace YaoiTests\Helper\Service;

use Yaoi\Service;
use YaoiTests\Helper\Service\BasicExposed;

class NoSettings extends BasicExposed
{
    protected static function getSettingsClassName() {
        return null;
    }
}