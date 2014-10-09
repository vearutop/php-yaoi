<?php

class View_HTML_Input extends View_HTML_Element {
    protected $tag = 'input';
    protected $value;

    public function setName($name) {
        return $this->setAttribute('name', $name);
    }

    public function setValue($value) {
        $this->value = $value;
        $this->setAttribute('value', $value);
        return $this;
    }

    public function setPlaceholder($placeholder) {
        $this->setAttribute('placeholder', $placeholder);
        return $this;
    }

    public function fillValue(&$source = null) {
        if (null === $source) {
            $source = &$_REQUEST;
        }
        if (isset($source[$this->attributes['name']])) {
            $this->setValue($source[$this->attributes['name']]);
        }
        return $this;
    }

    public function hidden() {
        $this->setAttribute('type', 'hidden');
        return $this;
    }
}