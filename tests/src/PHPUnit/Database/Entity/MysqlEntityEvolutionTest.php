<?php

namespace YaoiTests\PHPUnit\Database\Entity;

use Yaoi\Database;
use Yaoi\Log;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Database\CheckAvailable;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\User;

class MysqlComplexTest extends TestCase
{
    /** @var  Database */
    protected $database;

    public function setUp() {
        $this->database = CheckAvailable::getMysqli();
    }

    protected $expectedMigrationLog = <<<EOD
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
)
OK
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD COLUMN `age` int DEFAULT NULL,
ADD COLUMN `host_id` int NOT NULL,
ADD INDEX `key_age` (`age`),
ADD CONSTRAINT `432f6fb01e8766435a432e5ed8ffb2ef` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`)
OK
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD COLUMN `session_id` int NOT NULL,
ADD COLUMN `first_name` varchar(255) NOT NULL,
ADD COLUMN `last_name` varchar(255) NOT NULL,
DROP COLUMN `name`,
DROP COLUMN `host_id`,
ADD UNIQUE INDEX `unique_last_name_first_name` (`last_name`, `first_name`),
DROP INDEX `432f6fb01e8766435a432e5ed8ffb2ef`,
ADD CONSTRAINT `42405537c0e04845e2902c8a7fb322be` FOREIGN KEY (`session_id`) REFERENCES `yaoi_tests_entity_session` (`id`),
DROP FOREIGN KEY `432f6fb01e8766435a432e5ed8ffb2ef`
OK
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires deletion
OK

EOD;


    public function testUpdateSchema() {
        $logString = '';
        $log = Log::getInstance(function()use(&$logString){
            $settings = new Log\Settings();
            $settings->driverClassName = Log\Driver\String::className();
            $settings->storage = &$logString;
            return $settings;
        });

        User::$revision = 1;
        User::bindDatabase($this->database, true);

        //$this->database->log(new Log('stdout'));

        User::table()->migration()->rollback();
        Host::table()->migration()->apply();


        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::$revision = 2;
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::$revision = 3;
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::table()->migration()->setLog($log)->rollback();

        $this->assertStringEqualsCRLF($this->expectedMigrationLog, $logString);

    }
}