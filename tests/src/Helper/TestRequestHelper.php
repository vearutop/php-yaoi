<?php

namespace YaoiTests\Helper;

use Yaoi\Io\Request;

class TestRequestHelper
{
    /**
     * @param array $argv
     * @return Request
     */
    public static function getCliRequest($argv) {
        if (!is_array($argv)) {
            $argv = func_get_args();
        }

        $request = Request::__set_state(array(
            'baseUrl' => '/',
            'get' =>
                array(),
            'post' =>
                array(),
            'request' =>
                array(),
            'cookie' =>
                array(),
            'server' =>
                Request\Server::__set_state(array(
                    'SCRIPT_NAME' => './cli',
                    'SCRIPT_FILENAME' => './cli',
                    'PHP_SELF' => './cli',
                    'argv' => array_merge(array('script.php'), $argv),
                    'argc' => count($argv) + 1,
                )),
            'isCli' => true,
        ));
        return $request;
    }

}