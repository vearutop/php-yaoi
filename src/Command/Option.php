<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;
use Yaoi\String\Utils;

class Option extends BaseClass
{
    const TYPE_BOOL = 'bool';
    const TYPE_VALUE = 'value';
    const TYPE_ENUM = 'enum';

    public $values = array();

    public $name;
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setEnum($values) {
        $this->type = self::TYPE_ENUM;
        $values = is_array($values) ? $values : func_get_args();
        $this->values = array_combine($values, $values);
        return $this;
    }

    public function setType($type = Option::TYPE_VALUE) {
        $this->type = $type;
        return $this;
    }

    public $description;
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public $isRequired;
    public function setIsRequired($isRequired = true) {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function validateFilterValue($value) {
        if ($this->type === self::TYPE_ENUM) {
            if (!isset($this->values[$value])) {
                throw new Exception('Invalid value for `' . $this->name . '`: ' . $value .'. '
                    .'Allowed values: ' . implode(', ', $this->values) . '.', Exception::INVALID_VALUE);
            }
            else {
                return $value;
            }

        }
        else return $value;
    }


    public function getPublicName() {
        return Utils::fromCamelCase($this->name, '_');
    }

    public $isVariadic = false;
    public function setIsVariadic($yes = true) {
        $this->isVariadic = $yes;
        return $this;
    }

    public $isUnnamed = false;
    public function setIsUnnamed($isUnnamed = true) {
        $this->isUnnamed = $isUnnamed;
        if ($isUnnamed && self::TYPE_BOOL === $this->type) {
            $this->type = self::TYPE_VALUE;
        }
        return $this;
    }


    public $type = self::TYPE_BOOL;
}