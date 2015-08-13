<?php

namespace YaoiTests\Database;


use Yaoi\Database;

class CheckAvailable
{
    public static function checkMysqli() {
        if (!class_exists('mysqli', false)) {
            throw new \PHPUnit_Framework_SkippedTestError('Mysqli extension is not available');
        }
        try {
            Database::getInstance('test_mysqli')->query("SELECT VERSION()")->fetchRow();
        }
        catch (Database\Exception $e) {
            throw new \PHPUnit_Framework_SkippedTestError($e->getMessage());
        }
    }

    public static function checkPgsql() {
        if (!function_exists('pg_connect')) {
            throw new \PHPUnit_Framework_SkippedTestError('pg_connect() is not available');
        }
        try {
            Database::getInstance('test_pgsql');
        }
        catch (\Yaoi\Service\Exception $exception) {
            throw new \PHPUnit_Framework_SkippedTestError($exception->getMessage());
        }
    }


    public static function checkPdoPgsql() {
        if (extension_loaded('PDO')) {
            $drivers = pdo_drivers();
            if (!in_array('pgsql', $drivers)) {
                throw new \PHPUnit_Framework_SkippedTestError('PDO pgsql driver is not available.');
            }
        }
        else {
            throw new \PHPUnit_Framework_SkippedTestError('PDO extension is not available.');
        }

        try {
            Database::getInstance('test_pdo_pgsql');
        }
        catch (\Yaoi\Service\Exception $exception) {
            throw new \PHPUnit_Framework_SkippedTestError($exception->getMessage());
        }

    }

}