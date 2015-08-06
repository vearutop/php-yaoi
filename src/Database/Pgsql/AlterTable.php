<?php

namespace Yaoi\Database\Pgsql;

use Yaoi\Database\Definition\Index;
use Yaoi\Sql\Batch;
use Yaoi\Sql\Symbol;

class AlterTable extends \Yaoi\Sql\AlterTable
{
    /** @var  Batch */
    public $batch;

    protected function processIndexes() {
        $this->batch = new Batch();
        $this->batch->add($this);
        $beforeIndexes = $this->before->indexes;

        foreach ($this->after->indexes as $indexId => $index) {
            if (!isset($beforeIndexes[$indexId])) {
                $this->batch->add($this->database->expr('CREATE '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX ? ON ? (?)',
                    new Symbol($index->getName()), new Symbol($this->before->schemaName), $index->columns)
                );
            }
            else {
                unset($beforeIndexes[$indexId]);
            }
        }
        foreach ($beforeIndexes as $indexId => $index) {
            if ($index->type === Index::TYPE_UNIQUE) {
                $this->lines []= $this->database->expr('DROP CONSTRAINT ?', new Symbol($index->getName()));
            }
            else {
                $this->batch->add($this->database->expr('DROP INDEX ?', new Symbol($index->getName())));
            }
        }

    }


}