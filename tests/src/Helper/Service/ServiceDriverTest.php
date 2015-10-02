<?php

namespace YaoiTests\Helper\Service;

use Yaoi\Storage;

class TestServiceThree extends \Yaoi\Service {
    /**
     * @return \Yaoi\Service\Settings
     */
    protected static function getSettingsClassName()
    {
        return \Yaoi\Service\Settings::className();
    }

}
