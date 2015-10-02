<?php
namespace YaoiTests\PHPUnit\Migration;

use User;
use Yaoi\Database;
use Yaoi\Migration\ClosureMigration;
use Yaoi\Migration\Migration;
use Yaoi\Migration\Settings;
use Yaoi\Migration\Manager;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;

class Test extends TestCase
{

    public function testDoubleExecution()
    {
        $d = new Settings();
        $d->storage = new PhpVar();
        $manager = new Manager($d);

        $applied = array(
            'm1' => 0
        );

        $migration = new ClosureMigration('m1', function () use (&$applied) {
            $applied['m1']++;
        }, function () use (&$applied) {
            $applied['m1']--;
        });

        $manager->perform($migration);
        $this->assertSame(1, $applied['m1']);

        $manager->perform($migration);
        $this->assertSame(1, $applied['m1']);

        $manager->perform($migration, Migration::SKIP);
        $this->assertSame(1, $applied['m1']);

        $manager->perform($migration, Migration::ROLLBACK);
        $this->assertSame(0, $applied['m1']);

        $manager->perform($migration, Migration::ROLLBACK);
        $this->assertSame(0, $applied['m1']);

    }

    public function testIsApplied()
    {
        $d = new Settings();
        $d->storage = new PhpVar();
        $m = new Manager($d);

        $log = '';

        $migrationAlwaysApply = new ClosureMigration('m1', function () use (&$log) {
            $log .= 'a';
        }, function () use (&$log) {
            return;
        },
            true);

        $migrationAlwaysRollback = new ClosureMigration('m1', function () use (&$log) {
            return;
        }, function () use (&$log) {
            $log .= 'r';
        },
            true);


        $m->perform($migrationAlwaysApply)
            ->perform($migrationAlwaysApply)
            ->perform($migrationAlwaysApply, Migration::ROLLBACK)
            ->perform($migrationAlwaysApply, Migration::ROLLBACK);

        $this->assertSame('aa', $log);

        $log = '';
        $m->perform($migrationAlwaysRollback)
            ->perform($migrationAlwaysRollback)
            ->perform($migrationAlwaysRollback, Migration::ROLLBACK)
            ->perform($migrationAlwaysRollback, Migration::ROLLBACK);

        $this->assertSame('rr', $log);

    }

    public function testRun()
    {
        $settings = new Settings();
        $settings->storage = new PhpVar();
        $log = '';
        $manager = new Manager($settings);
        $manager->add(
            array(
                new ClosureMigration('t1', function () use (&$log) {
                    $log .= 'at1';
                }),

                new ClosureMigration('t2', function () use (&$log) {
                    $log .= 'at2';
                })
            )
        );

        $manager->run();

        $this->assertSame('at1at2', $log);
    }

    public function testGlobalMigrations()
    {
        return;
        // fake doc test
        Manager::register(function () {
            $dsn = new Settings();
            $dsn->storage = 'serialized-file:///conf/migrations.lock';
            $dsn->run = function (Manager $m) {
                // Add migration for TASK-1234
                $m->perform(new ClosureMigration('TASK-1234', function () {
                    Database::getInstance()->query("ALTER TABLE `table1` ADD COLUMN `field` CHAR(1) DEFAULT NULL");
                }));

                // Rollback migrations for TASK-1123
                $m->perform(new ClosureMigration('TASK-1123', function () {
                    Database::getInstance()->query("ALTER TABLE `table2` ADD COLUMN `field2` CHAR(1) DEFAULT NULL");
                }, function () {
                    Database::getInstance()->query("ALTER TABLE `table2` DROP COLUMN `field2`");
                }), Migration::ROLLBACK);

                $m->perform(User::migrationCreateTable());
            };


            return $dsn;
        });
    }
}
