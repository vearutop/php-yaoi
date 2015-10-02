<?php
namespace YaoiTests\PHPUnit\Database\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Log;
use Yaoi\Migration\Manager;
use Yaoi\Migration\Migration;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\Session;
use YaoiTests\Helper\Entity\SessionTag;
use YaoiTests\Helper\Entity\Tag;
use YaoiTests\Helper\Entity\OneABBR;
use YaoiTests\Helper\Entity\Two;

abstract class TestBase extends \Yaoi\Test\PHPUnit\TestCase
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
        $table = OneABBR::table();
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
        $this->assertSame('yaoi_tests_helper_entity_one_abbr', OneABBR::table()->schemaName);
        $this->assertSame('custom_name', Two::table()->schemaName);
    }

    /**
     * @see Yaoi\Database\Definition\Table::schemaName
     */
    public function testClassName() {
        $this->assertSame('YaoiTests\Helper\Entity\OneABBR', OneABBR::table()->className);
        $this->assertSame('YaoiTests\Helper\Entity\Two', Two::table()->className);
    }


    protected $entityOneCreateTableExpected;
    protected $entityTwoCreateTableExpected;


    public function testCreateTable() {
        $this->assertStringEqualsCRLF(
            $this->entityOneCreateTableExpected,
            (string)$this->database->getUtility()
                ->generateCreateTableOnDefinition(OneABBR::table()));

        $this->assertStringEqualsCRLF(
            $this->entityTwoCreateTableExpected,
            (string)$this->database->getUtility()
                ->generateCreateTableOnDefinition(Two::table()));

    }



    protected $expectedMigrateLog = <<<EOD
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is already non-existent
Rollback, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is already non-existent
Rollback, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is already non-existent
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) requires migration
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
 CONSTRAINT `fk_yaoi_tests_entity_session_host_id_yaoi_tests_entity_host_id` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
 PRIMARY KEY (`id`)
)
OK
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
Apply, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) requires migration
CREATE TABLE `yaoi_tests_entity_tag` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)
OK
Apply, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
Dependent migration required
Apply, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is up to date
CREATE TABLE `yaoi_tests_entity_session_tag` (
 `session_id` int NOT NULL,
 `tag_id` int NOT NULL,
 `added_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (`session_id`, `tag_id`)
)
OK
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
Apply, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is up to date
Apply, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is up to date
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) requires deletion
OK
OK
Rollback, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is already non-existent
OK
Rollback, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent
OK
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is already non-existent
Rollback, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is already non-existent
Rollback, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is already non-existent
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent

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
        foreach ($tables as $table) {
            $remover->add($table->migration(), Migration::ROLLBACK);
        }
        $remover->run();
        $remover->setLog($log);


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

        ob_start();
        $adder->run();
        ob_end_clean();

        $this->assertStringEqualsCRLF($this->expectedMigrateLog, $logData);
    }


    protected $expectedBindsStatement = <<<SQL
SELECT `yaoi_tests_entity_tag`.`id`, `yaoi_tests_entity_tag`.`name`
FROM `yaoi_tests_entity_host`
LEFT JOIN `yaoi_tests_entity_session` ON `yaoi_tests_entity_session`.`host_id` = `yaoi_tests_entity_host`.`id`
LEFT JOIN `yaoi_tests_entity_session_tag` ON `yaoi_tests_entity_session`.`id` = `yaoi_tests_entity_session_tag`.`session_id`
LEFT JOIN `yaoi_tests_entity_tag` ON `yaoi_tests_entity_tag`.`id` = `yaoi_tests_entity_session_tag`.`tag_id`
WHERE `yaoi_tests_entity_host`.`id` = 12
GROUP BY `yaoi_tests_entity_tag`.`id`
SQL;

    public function testBinds() {
        $select = Host::statement()
            ->select(Tag::columns())
            ->where('? = ?', Host::columns()->id, 12)
            ->leftJoin('? ON ? = ?', Session::table(), Session::columns()->hostId, Host::columns()->id)
            ->leftJoin('? ON ? = ?', SessionTag::table(), Session::columns()->id, SessionTag::columns()->sessionId)
            ->leftJoin('? ON ? = ?', Tag::table(), Tag::columns()->id, SessionTag::columns()->tagId)
            ->groupBy(Tag::columns()->id)
            ->bindResultClass(Tag::className());

        $this->assertStringEqualsSpaceless($this->expectedBindsStatement, $select->build());
    }


    /**
     * @see Entity::save
     * @see Entity::delete
     * @see Entity::find
     * @throws \Yaoi\Entity\Exception
     */
    public function testLifeCycle() {
        $host = new Host();
        $host->name = 'test';
        $host->save();
        $this->assertSame(1, $host->id);

        $host->name = 'Test';
        $host->save();

        $host2 = new Host();
        $host2->name = 'Test 2';
        $host2->save();
        $this->assertSame(2, $host2->id);

        $this->assertSame('Test', Host::find($host->id)->name);
        $host->delete();

        $this->assertNull(Host::find($host->id));

    }


    public function testFindOrSave() {
        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->tagId = 456;

        $sessionTag->findOrSave();
    }

    /**
     * @expectedException \Yaoi\Entity\Exception
     * @expectedExceptionCode \Yaoi\Entity\Exception::KEY_MISSING
     * @throws \Yaoi\Entity\Exception
     */
    public function testFindFullPrimaryRequired() {
        SessionTag::find(123);
    }
    /**
     * @expectedException \Yaoi\Entity\Exception
     * @expectedExceptionCode \Yaoi\Entity\Exception::KEY_MISSING
     * @throws \Yaoi\Entity\Exception
     */
    public function testUpdateFullPrimaryRequired() {
        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->addedAt = time();
        $sessionTag->update();
    }

    /**
     * @expectedException \Yaoi\Entity\Exception
     * @expectedExceptionCode \Yaoi\Entity\Exception::KEY_MISSING
     * @throws \Yaoi\Entity\Exception
     */
    public function testDeleteFullPrimaryRequired() {
        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->delete();
    }






    public function setUp() {
        Host::bindDatabase($this->database);
        Session::bindDatabase($this->database);
        SessionTag::bindDatabase($this->database);
        Tag::bindDatabase($this->database);
    }


}