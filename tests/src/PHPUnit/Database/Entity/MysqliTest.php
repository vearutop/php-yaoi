<?php

namespace YaoiTests\PHPUnit\Database\Entity;

class MysqliTest extends \YaoiTests\PHPUnit\Database\Entity\TestBase
{

    protected $entityOneCreateTableExpected = <<<SQL
CREATE TABLE `yaoi_tests_helper_entity_one_abbr` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `address` varchar(255) DEFAULT '',
 `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)
SQL;

    protected $entityTwoCreateTableExpected = "CREATE TABLE `custom_name` (
 `id` int NOT NULL AUTO_INCREMENT,
 `one_id` int NOT NULL,
 `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `info` varchar(255) DEFAULT NULL,
 KEY `key_info` (`info`),
 CONSTRAINT `fk_custom_name_one_id_yaoi_tests_helper_entity_one_abbr_id` FOREIGN KEY (`one_id`) REFERENCES `yaoi_tests_helper_entity_one_abbr` (`id`),
 PRIMARY KEY (`id`)
)";

    public function setUp() {
        $this->database = \YaoiTests\Helper\Database\CheckAvailable::getMysqli();
        parent::setUp();
    }

}