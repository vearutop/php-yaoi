<?php

namespace YaoiTests\PHPUnit\Database;

use Yaoi\Database;

class PdoPgsqlTest extends PgsqlTest
{

    protected function setUp()
    {
        if (extension_loaded('PDO')) {
            $drivers = pdo_drivers();
            if (!in_array('pgsql', $drivers)) {
                $this->markTestSkipped('PDO pgsql driver is not available.');
            }
        } else {
            $this->markTestSkipped('PDO extension is not available.');
        }

        try {
            $this->db = Database::getInstance('test_pdo_pgsql');
        } catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }

}