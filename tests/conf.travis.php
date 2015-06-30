<?php

use Yaoi\Database;
use Yaoi\Log;
use Yaoi\Migration;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;

Database::register('mysqli://root@localhost/test1');
Database::register(\Yaoi\Service::PRIMARY, 'test_mysqli');
Database::register('pgsql://postgres@localhost/travis_ci_test', 'test_pgsql');
Database::register('pdo-pgsql://postgres@localhost/travis_ci_test', 'test_pdo_pgsql');
Log::register('stdout');
error_reporting(E_ALL);
ini_set('display_errors', 1);

TestCase::$settings['envHttpPHPServer'] = '127.0.0.1:8000';
TestCase::$settings['envHttpMirrorServer'] = '127.0.0.1:1337';
TestCase::$settings['envMemcache'] = extension_loaded('memcache');
TestCase::$settings['envMongo'] = extension_loaded('mongo');

Migration\Manager::register(function () {
    $dsn = new Migration\Settings();
    $dsn->storage = new PhpVar();
    return $dsn;
});
