<?php

class View_HTMLTable extends View_HTMLElement implements View_TableRenderer {
    public $optionEscapeHTML = false;

    protected $tag = 'table';
    public $content = array();

    public function add($row) {
        $this->content []= $row;
        return $this;
    }

    public function setRows(&$rows)
    {
        $this->content = $rows;
        return $this;
    }


    public function render() {
        echo '<table>', "\n";
        $keys = array();
        foreach ($this->content as $row) {
            if (!$keys) {
                echo '<tr>';
                foreach ($row as $key => $value) {
                    $keys []= $key;
                    echo '<th>', $key, '</th>';
                }
                echo '</tr>', "\n";
            }

            echo '<tr>';
            foreach ($keys as $key) {
                $value = array_key_exists($key, $row) ? $row[$key] : '';
                if (null === $value) {
                    $value = 'NULL';
                }
                if ($this->optionEscapeHTML) {
                    $value = str_replace('<', '&lt;', $value);
                }
                echo '<td>', $value, '</td>';
            }
            echo '</tr>', "\n";
        }


        echo '</table>';
        return $this;
    }
} 