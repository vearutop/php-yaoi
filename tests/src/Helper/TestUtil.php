<?php

namespace YaoiTests\Helper;


class TestUtil
{
    public static function getResourcePath() {
        static $path = null;
        if (null === $path) {
            $path = __DIR__ . '/../../resources/';
        }
        return $path;
    }

    public static function getMocksPath() {
        static $path = null;
        if (null === $path) {
            $path = self::getResourcePath() . 'mocked-data-sets/';
        }
        return $path;
    }

}