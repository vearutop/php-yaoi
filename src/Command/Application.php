<?php

namespace Yaoi\Command;

use Yaoi\Command;

abstract class Application extends Command implements Command\Application\Contract
{
    public $action;

    static function setUpDefinition(Definition $definition, $options)
    {
        $commandDefinitions = new \stdClass();
        static::setUpCommands($definition, $commandDefinitions);
        $actions = array();



        foreach ((array)$commandDefinitions as $name => $commandDefinition) {
            $actions []= $name;
            $definition->actions [$name]= $commandDefinition;
        }

        $options->action = Option::create()
            ->setEnum($actions)
            ->setDescription('Action name')
            ->setIsRequired()
            ->setIsUnnamed();
    }

    public function performAction()
    {
        // no op
        // TODO refactor interfaces to remove this method for Application
    }
}