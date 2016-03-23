<?php

namespace Yaoi\Command\Web;

use Yaoi\Command\Option;
use Yaoi\Command;
use Yaoi\Command\RequestMapperContract;
use Yaoi\Io\Request;
use Yaoi\String\Expression;
use Yaoi\String\Utils;

class RequestMapper implements RequestMapperContract
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    private function readUnnamedOption(Option $option)
    {
        if ($option->type === Option::TYPE_ENUM) {
            $option->setEnumMapper(self::$unnamedMapper);
        }

        $value = array();

        while (false !== $item = $this->getUnnamed()) {
            $item = $option->validateFilterValue($item);

            if (!$option->isVariadic) {
                $value = $item;
                break;
            } else {
                $value [] = $item;
            }
        }

        if (!$value) {
            $value = false;
        }

        return $value;
    }


    private function readNamedOption(Option $option)
    {
        $publicName = self::$namedMapper->__invoke($option->name);
        $value = $this->request->request($publicName, false);

        if ($option->type === Option::TYPE_ENUM) {
            $option->setEnumMapper(self::$namedMapper);
        }

        $value = $option->validateFilterValue($value);

        if ($option->isVariadic) {
            if (!is_array($value)) {
                $value = array($value);
            }
        }

        return $value;
    }


    /** @var \Closure */
    private static $unnamedMapper;
    /** @var \Closure */
    private static $namedMapper;

    /**
     * @param Option[] $commandOptions
     * @return \stdClass
     * @throws Command\Exception
     */
    public function readOptions(array $commandOptions)
    {
        $commandState = new \stdClass();

        foreach ($commandOptions as $option) {
            if ($option->isUnnamed) {
                $value = $this->readUnnamedOption($option);
            }
            else {
                $value = $this->readNamedOption($option);
            }

            if (false !== $value) {
                $commandState->{$option->name} = $value;
            }

            else {
                if ($option->isRequired) {
                    throw new Command\Exception('Option ' . $option->name . ' required',
                        Command\Exception::OPTION_REQUIRED);
                }
            }
        }

        return $commandState;

    }


    private $unnamedValues;
    private function getUnnamed() {
        if (null === $this->unnamedValues) {
            $this->unnamedValues = explode('/', trim($this->request->path(), '/'));
        }
        if (!$this->unnamedValues) {
            return false;
        }
        else {
            return array_shift($this->unnamedValues);
        }
    }

    /**
     * @param array $properties
     * @return Expression
     */
    public function makeAnchor(array $properties)
    {
        $unnamed = array();
        $unnamedTemplate = '';

        $queryTemplate = '';
        $query = array();

        foreach ($properties as $property) {
            /** @var Option $option */
            list($option, $value) = $property;

            if ($option->isUnnamed) {
                $unnamedTemplate .= '/??';
                $unnamed[] = self::$unnamedMapper->__invoke($value);
            }
            else {
                $queryTemplate .= '&' . self::$namedMapper->__invoke($option->name) . '=??';
                $query[] = $value;
            }
        }

        $template = $unnamedTemplate;
        $binds = $unnamed;
        if ($queryTemplate) {
            $template .= '?' . substr($queryTemplate, 1);
            $binds = $binds + $query;
        }

        $expression = new Expression($template, $binds);
        $expression->setPlaceholder('??');
        return $expression;
    }

    public function getExportName(Option $option)
    {
        if ($option->isUnnamed) {
            return self::$unnamedMapper->__invoke($option->name);
        }
        else {
            return self::$namedMapper->__invoke($option->name);
        }
    }

    public static function setupMappers() {
        self::$unnamedMapper = function($name){
            return Utils::fromCamelCase($name, '-');
        };

        self::$namedMapper = function($name) {
            return Utils::fromCamelCase($name, '_');
        };
    }
}
RequestMapper::setupMappers();
