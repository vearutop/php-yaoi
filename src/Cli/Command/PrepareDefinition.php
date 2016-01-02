<?php

namespace Yaoi\Cli\Command;

use Yaoi\BaseClass;
use Yaoi\Cli\Exception;
use Yaoi\Cli\Option;
use Yaoi\Io\Content\Info;

class PrepareDefinition extends BaseClass
{
    /** @var Option[] */
    public $requiredArguments = array();

    /** @var Option[] $optionalArguments */
    public $optionalArguments = array();

    /** @var Option[] $byShortName */
    public $byShortName = array();

    /** @var Option[] $byName */
    public $byName = array();

    /** @var Option[] $requiredOptions */
    public $requiredOptions = array();


    /** @var  Option[] */
    public $optionsArray;

    public function __construct($options)
    {
        $this->optionsArray = $options;

        /** @var Option $hasVariadicArgument */
        $hasVariadicArgument = null;

        $this->optionsArray[Runner::HELP] = Option::create()
            ->setDescription('Show usage information')
            ->setGroup(Runner::GROUP_MISC)
            ->setName(Runner::HELP);

        $this->optionsArray[Runner::VERSION] = Option::create()
            ->setDescription('Show version')
            ->setGroup(Runner::GROUP_MISC)
            ->setName(Runner::VERSION);

        $this->optionsArray[Runner::BASH_COMPLETION] = Option::create()
            ->setDescription('Generate bash completion')
            ->setGroup(Runner::GROUP_MISC)
            ->setName(Runner::BASH_COMPLETION);

        $this->optionsArray[Runner::INSTALL] = Option::create()
            ->setDescription('Install to /usr/local/bin/')
            ->setGroup(Runner::GROUP_MISC)
            ->setName(Runner::INSTALL);


        foreach ($this->optionsArray as &$option) {
            $option = Option::cast($option);
            if ($option->isUnnamed) {
                if ($hasVariadicArgument) {
                    throw new Exception('Non-tailing variadic argument ' . $hasVariadicArgument->getUsage(),
                        Exception::NON_TAILING_VARIADIC_ARGUMENT);
                }

                if ($option->isVariadic) {
                    $hasVariadicArgument = $option;
                }

                if ($option->isRequired) {
                    if (!empty($this->optionalArguments)) {
                        throw new Exception('Non-tailing optional argument', Exception::NON_TAILING_OPTIONAL_ARGUMENT);
                    }
                    $this->requiredArguments [] = $option;
                } else {
                    $this->optionalArguments [] = $option;
                }
                continue;
            }

            if ($option->isRequired) {
                $this->requiredOptions[$option->name] = $option;
            }

            if ($option->shortName) {
                $this->byShortName[$option->shortName] = $option;
            }

            $this->byName[$option->getPublicName()] = $option;
        }

    }


    public $argumentsDescription = array();
    public $optionsDescription = array();
    public $usage = '';

    public function initOptions()
    {
        foreach ($this->optionsArray as $name => $option) {
            if ($option instanceof Option) {
                if ($option->isUnnamed || $option->isRequired) {
                    $this->usage .= ' ' . $option->getUsage();
                }

                $description = $option->description;
                if ($option->type === Option::TYPE_ENUM) {
                    $description .= ($description ? PHP_EOL : '') . 'Allowed values: ' . implode(', ', $option->values);
                }

                if ($option->isUnnamed) {
                    $this->argumentsDescription [] = array(new Info($option->name), $description);

                } else {
                    $this->optionsDescription [$option->group][] = array(new Info($option->getUsage()), $description);
                }
            }
        }
    }

}