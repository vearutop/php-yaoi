<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandWithRequiredOption extends Command
{
    public $required;
    public $optional;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->required = Command\Option::create()->setIsRequired()->setType();
        $options->optional = Command\Option::create();
    }

    public function performAction()
    {
        // no op
    }

}