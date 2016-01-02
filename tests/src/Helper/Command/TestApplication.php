<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command\Application;
use Yaoi\Command\Definition;

class TestApplication extends Application
{
    public $actionOne;
    public $actionTwo;
    public $actionThree;

    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $definition->name = 'test-application';
        $definition->description = 'Test application description';
        $definition->version = 'v1.0';

        /**
         * @see Application @testdoc
         * Each @see Application  action is set by
         * @see Command definition
         */
        $commandDefinitions->actionOne = TestCommandWithRequiredArgument::definition();
        $commandDefinitions->actionTwo = TestCommandWithOptionValue::definition();
        $commandDefinitions->actionThree = TestCommandWithVersion::definition();
    }

}