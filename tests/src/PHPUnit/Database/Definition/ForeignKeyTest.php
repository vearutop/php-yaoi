<?php

namespace YaoiTests\PHPUnit\Database\Definition;

use Yaoi\Database\Definition\ForeignKey;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Entity\Session;
use YaoiTests\Helper\Entity\SessionTag;
use YaoiTests\Helper\Entity\Tag;

class ForeignKeyTest extends TestCase
{

    /**
     * Local and reference columns count must match, otherwise exception is thrown
     *
     * @see ForeignKey::__construct
     * @expectedException \Yaoi\Database\Definition\Exception
     * @expectedExceptionCode \Yaoi\Database\Definition\Exception::FK_COUNT_MISMATCH
     */
    public function testColumnMismatch() {
        $localColumns = array(SessionTag::columns()->sessionId, SessionTag::columns()->tagId);
        $referenceColumns = array(Session::columns()->id);
        new ForeignKey($localColumns, $referenceColumns);
    }

    /**
     * If FK name is longer than 64 chars, it is hashed with MD5
     * @see ForeignKey::getName
     */
    public function testLongName() {
        $localColumns = array(SessionTag::columns()->sessionId, SessionTag::columns()->tagId);
        $referenceColumns = array(Session::columns()->id, Tag::columns()->id);
        $foreignKey = new ForeignKey($localColumns, $referenceColumns);
        $this->assertSame('90e2866a506c1bf228b66310920c52ef', $foreignKey->getName());
    }
}