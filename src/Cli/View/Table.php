<?php

namespace Yaoi\Cli\View;

use Yaoi\View\Hardcoded;
use Yaoi\Io\Content\Renderer;
use Yaoi\Io\Content;

class Table extends Hardcoded implements Renderer
{
    private $colDelimiter = '   ';
    private $rowDelimiter = null;
    /** @var \Iterator  */
    private $rowsIterator;

    public function __construct(\Iterator $rows)
    {
        $this->rowsIterator = $rows;
    }

    public function setColDelimiter($delimiter = '   ') {
        $this->colDelimiter = $delimiter;
        return $this;
    }

    public function setRowDelimiter($delimiter = null) {
        $this->rowDelimiter = $delimiter;
        return $this;
    }

    private $showHeader = false;
    public function setShowHeader($yes = true) {
        $this->showHeader = $yes;
        return $this;
    }

    private $lines = array();
    private $length = array();
    private $keys = array();
    private $rows = array();
    private $rowDelimiterLine;

    private function findLines()
    {
        foreach ($this->rows as $rowIndex => $row) {
            foreach ($row as $key => $value) {
                if (!$value instanceof Content\Text) {
                    $value = new Content\Text($value);
                }
                $renderer = new Text($value);
                foreach ($renderer->lines() as $lineIndex => $line) {
                    $stringLength = strlen($line->text->value);
                    if (!isset($this->length[$key]) || $this->length[$key] < $stringLength) {
                        $this->length[$key] = $stringLength;
                    }
                    $this->lines [$rowIndex][$lineIndex][$key] = $line;
                }
            }
        }
    }

    private function findKeys()
    {
        $this->keys = array();

        $this->rows = array(0 => array());
        foreach ($this->rowsIterator as $rowIndex => $row) {
            $this->rows [] = $row;

            foreach ($row as $key => $value) {
                $this->keys [$key] = $key;
            }
        }
        if ($this->showHeader) {
            $this->rows[0] = $this->keys;
        }
    }

    public function echoLines()
    {
        foreach ($this->lines as $rowIndex => $rowData) {
            foreach ($rowData as $lineIndex => $row) {
                $line = '';
                foreach ($this->length as $key => $maxLength) {
                    /** @var \Yaoi\Cli\View\Text $value */
                    $value = isset($row[$key]) ? $row[$key] : null;

                    if ($line) {
                        $line .= $this->colDelimiter;
                    }
                    if ($value) {
                        $value->strPad($maxLength);
                        $line .= $value;
                    } else {
                        $line .= str_repeat(' ', $maxLength);
                    }
                }
                echo $line, PHP_EOL;
                if ($this->rowDelimiter) {
                    if (null === $this->rowDelimiterLine) {
                        $lineLength = strlen($line);
                        $repeat = ceil($lineLength / strlen($this->rowDelimiter));
                        $rowDelimiterLine = str_repeat($this->rowDelimiter, $repeat);
                        if (strlen($rowDelimiterLine) > $lineLength) {
                            $this->rowDelimiterLine = substr($rowDelimiterLine, 0, $lineLength);
                        }
                    }
                    echo $this->rowDelimiterLine, PHP_EOL;
                }
            }
        }
    }

    public function render()
    {
        $this->rowDelimiterLine = null;
        $this->findKeys();
        $this->findLines();
        $this->echoLines();
    }
}