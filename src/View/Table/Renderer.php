<?php

namespace Yaoi\View\Table;

use Iterator;
use Yaoi\View\Exception;
use Yaoi\View\HTML\Element;

abstract class Renderer extends Element
{
    /** @var array|Iterator */
    protected $rows = array();

    public function __construct(&$rows = null)
    {
        if (null !== $rows) {
            $this->setRows($rows);
        }
    }

    public function setRows(&$rows)
    {
        if (!is_array($rows) && !$rows instanceof Iterator) {
            throw new Exception('Wrong data type', Exception::WRONG_DATA_TYPE);
        }
        $this->rows = &$rows;
        return $this;
    }


}