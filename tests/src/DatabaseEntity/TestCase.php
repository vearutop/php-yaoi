<?php
namespace YaoiTests\DatabaseEntity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Log;
use Yaoi\Migration\ClosureMigration;
use Yaoi\Migration\Manager;
use Yaoi\Migration\Migration;
use Yaoi\Storage\PhpVar;
use YaoiTests\Entity\Host;
use YaoiTests\Entity\Session;
use YaoiTests\Entity\SessionTag;
use YaoiTests\Entity\Tag;
use YaoiTests\EntityOneABBR;
use YaoiTests\EntityTwo;

abstract class TestCase extends \Yaoi\Test\PHPUnit\TestCase
{
    /** @var  \Yaoi\Database */
    protected $database;

    /**
     * By default column has STRING type
     * @see Column::__construct
     */
    public function testDefaultColumn() {
        $this->assertSame(Column::STRING, Column::create()->flags);
    }


    /**
     * AUTO_ID column is INTEGER by default
     * @see Column::__construct
     */
    public function testAutoIdColumn() {
        $this->assertSame(Column::AUTO_ID + Column::INTEGER + Column::NOT_NULL, Column::create(Column::AUTO_ID)->flags);
        $this->assertSame(Column::AUTO_ID + Column::INTEGER + Column::SIZE_4B,
            Column::create(Column::AUTO_ID + Column::INTEGER + Column::SIZE_4B)->flags);
    }

    /**
     * AUTO_ID column is primary key
     * @see Column::__construct
     * @todo throw exception on multiple AUTO_ID and setting custom PK when AUTO_ID is set
     */
    public function testAutoIdPrimary() {
        $columns = new \stdClass();
        $columns->id = new Column(Column::AUTO_ID);

        $table = new Table($columns, $this->database, 'some_table');
        $this->assertSame(array('id' => $columns->id), $table->primaryKey);
    }


    public function testColumns() {
        $table = EntityOneABBR::table();
        $columnsFlags = array();
        foreach ($table->getColumns(true) as $column) {
            $columnsFlags[$column->propertyName] = $column->flags;
        }

        $this->assertArrayBitwiseAnd(array(
            'id' => Column::AUTO_ID + Column::INTEGER,
            'name' => Column::STRING + Column::NOT_NULL,
            'address' => Column::STRING,
            'createdAt' => Column::TIMESTAMP,
        ), $columnsFlags);
    }


    /**
     * @see Yaoi\Database\Definition\Table::schemaName
     */
    public function testSchemaName() {
        $this->assertSame('yaoi_tests_entity_one_abbr', EntityOneABBR::table()->schemaName);
        $this->assertSame('custom_name', EntityTwo::table()->schemaName);
    }

    /**
     * @see Yaoi\Database\Definition\Table::schemaName
     */
    public function testClassName() {
        $this->assertSame('YaoiTests\EntityOneABBR', EntityOneABBR::table()->className);
        $this->assertSame('YaoiTests\EntityTwo', EntityTwo::table()->className);
    }


    protected $entityOneCreateTableExpected;
    protected $entityTwoCreateTableExpected;


    public function testCreateTable() {
        $this->assertStringEqualsCRLF(
            $this->entityOneCreateTableExpected,
            (string)$this->database->getUtility()
                ->generateCreateTableOnDefinition(EntityOneABBR::table()));

        $this->assertStringEqualsCRLF(
            $this->entityTwoCreateTableExpected,
            (string)$this->database->getUtility()
                ->generateCreateTableOnDefinition(EntityTwo::table()));

    }



    protected $expectedMigrateLog = <<<EOD
Rollback, table yaoi_tests_entity_session (YaoiTests\Entity\Session) is already non-existent
Rollback, table yaoi_tests_entity_host (YaoiTests\Entity\Host) is already non-existent
Rollback, table yaoi_tests_entity_tag (YaoiTests\Entity\Tag) is already non-existent
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Entity\SessionTag) is already non-existent
Apply, table yaoi_tests_entity_session (YaoiTests\Entity\Session) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Entity\Host) requires migration
CREATE TABLE `yaoi_tests_entity_host` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)
OK
CREATE TABLE `yaoi_tests_entity_session` (
 `id` int NOT NULL AUTO_INCREMENT,
 `host_id` int NOT NULL,
 `started_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `ended_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 CONSTRAINT `fk_yaoi_tests_entity_session_host_id_yaoi_tests_entity_host_id` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`),
 PRIMARY KEY (`id`)
)
OK
Apply, table yaoi_tests_entity_host (YaoiTests\Entity\Host) is up to date
Apply, table yaoi_tests_entity_tag (YaoiTests\Entity\Tag) requires migration
CREATE TABLE `yaoi_tests_entity_tag` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)
OK
Apply, table yaoi_tests_entity_session_tag (YaoiTests\Entity\SessionTag) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Entity\Session) is up to date
Dependent migration required
Apply, table yaoi_tests_entity_tag (YaoiTests\Entity\Tag) is up to date
CREATE TABLE `yaoi_tests_entity_session_tag` (
 `session_id` int NOT NULL,
 `tag_id` int NOT NULL,
 `added_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 CONSTRAINT `262487835611c7b057e16bd498a1117f` FOREIGN KEY (`session_id`) REFERENCES `yaoi_tests_entity_session` (`id`),
 CONSTRAINT `fk_yaoi_tests_entity_session_tag_tag_id_yaoi_tests_entity_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `yaoi_tests_entity_tag` (`id`),
 PRIMARY KEY (`session_id`, `tag_id`)
)
OK
Apply, table yaoi_tests_entity_session (YaoiTests\Entity\Session) is up to date
Apply, table yaoi_tests_entity_host (YaoiTests\Entity\Host) is up to date
Apply, table yaoi_tests_entity_tag (YaoiTests\Entity\Tag) is up to date
Apply, table yaoi_tests_entity_session_tag (YaoiTests\Entity\SessionTag) is up to date
Rollback, table yaoi_tests_entity_session (YaoiTests\Entity\Session) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Entity\SessionTag) requires deletion
OK
OK
Rollback, table yaoi_tests_entity_host (YaoiTests\Entity\Host) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session (YaoiTests\Entity\Session) is already non-existent
OK
Rollback, table yaoi_tests_entity_tag (YaoiTests\Entity\Tag) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Entity\SessionTag) is already non-existent
OK
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Entity\SessionTag) is already non-existent
Rollback, table yaoi_tests_entity_session (YaoiTests\Entity\Session) is already non-existent
Rollback, table yaoi_tests_entity_host (YaoiTests\Entity\Host) is already non-existent
Rollback, table yaoi_tests_entity_tag (YaoiTests\Entity\Tag) is already non-existent
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Entity\SessionTag) is already non-existent

EOD;

    public function testMigrate() {
        /** @var Table[] $tables */
        $tables = array(
            Session::table(),
            Host::table(),
            Tag::table(),
            SessionTag::table(),
        );

        $log = new Log('stdout');

        $remover = new Manager();
        $remover->setLog($log);
        foreach ($tables as $table) {
            $remover->add($table->migration(), Migration::ROLLBACK);
        }


        $adder = new Manager();
        $adder->setLog($log);
        foreach ($tables as $table) {
            $adder->add($table->migration());
        }

        ob_start();
        $remover->run();
        $adder->run();
        $adder->run();
        $remover->run();
        $remover->run();
        $logData = ob_get_clean();

        $this->assertStringEqualsCRLF($this->expectedMigrateLog, $logData);
    }


    public function setUp() {
        Host::bindDatabase($this->database);
        Session::bindDatabase($this->database);
        SessionTag::bindDatabase($this->database);
        Tag::bindDatabase($this->database);
    }


}