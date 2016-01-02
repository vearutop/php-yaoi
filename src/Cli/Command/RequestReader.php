<?php

namespace Yaoi\Cli\Command;

use Yaoi\BaseClass;
use Yaoi\Cli\Exception;
use Yaoi\Command;
use Yaoi\Command\Option;
use Yaoi\Io\Request;
use Yaoi\String\StringValue;

class RequestReader extends BaseClass
{
    public $scriptName;
    public $showHelp;
    public $showVersion;
    public $showBashCompletion;

    public $values = array();

    public $skipFirstTokens = 0;


    private $variadicStarted = false;
    /** @var \Yaoi\Cli\Option */
    private $option;
    /** @var PrepareDefinition */
    private $def;
    private $valueRequired = false;
    private $variadicValues = array();
    /** @var StringValue */
    private $token;


    private function processOption()
    {
        if ($this->option->isRequired) {
            unset($this->def->requiredOptions[$this->option->name]);
        }

        if ($this->option->type === Option::TYPE_BOOL) {
            $this->values[$this->option->name] = true;
        }
        elseif ($this->option->isVariadic) {
            $this->variadicStarted = true;
            $this->variadicValues = array();
        }
        else {
            $this->valueRequired = true;
        }

        if (!$this->option->isUnnamed) {
            $this->def->optionalArguments = array();
        }
        else {
            if ($this->option->isVariadic) {
                $this->continueVariadic();
            } else {
                $this->valueRequired();
            }
        }
    }

    private function valueRequired()
    {
        $this->values[$this->option->name] = $this->option->validateFilterValue((string)$this->token);
        $this->valueRequired = false;
    }

    private function finishVariadic()
    {
        $this->values[$this->option->name] = $this->variadicValues;
        $this->variadicValues = array();
        $this->variadicStarted = false;
    }

    private function continueVariadic()
    {
        $this->variadicValues [] = $this->option->validateFilterValue((string)$this->token);
    }

    private function processToken()
    {
        $optionFound = null;
        if (($optionName = $this->token->afterStarts(Runner::OPTION_NAME)) && isset($this->def->byName[$optionName])) {
            $optionFound = $this->def->byName[$optionName];
        } elseif (($optionName = $this->token->afterStarts(Runner::OPTION_SHORT)) && isset($this->def->byShortName[$optionName])) {
            $optionFound = $this->def->byShortName[$optionName];
        }

        if ($this->variadicStarted && $optionFound) {
            if (empty($this->variadicValues)) {
                throw new Exception('Unexpected option, value required', Exception::VALUE_REQUIRED);
            }
            $this->finishVariadic();
        }

        if (!empty($this->def->requiredArguments) && $optionFound) {
            throw new Exception('Unexpected option, argument required', Exception::ARGUMENT_REQUIRED);
        }

        if ($this->variadicStarted) {
            $this->continueVariadic();
            return;
        }

        if ($this->valueRequired) {
            if ($optionFound) {
                throw new Exception('Unexpected option, value required', Exception::VALUE_REQUIRED);
            }
            $this->valueRequired();
            return;
        }

        if (!empty($this->def->requiredArguments)) {
            $this->option = array_shift($this->def->requiredArguments);
            $this->processOption();
            return;
        }

        if ($optionFound) {
            $this->option = $optionFound;
            $this->processOption();
            return;
        }

        if ($this->def->optionalArguments) {
            $this->option = array_shift($this->def->optionalArguments);
            $this->processOption();
            return;
        }

        throw new Exception('Unexpected token: ' . $this->token, Exception::UNKNOWN_OPTION);
    }

    public function read(Request $request, array $options)
    {
        $this->def = new PrepareDefinition($options);

        $tokens = $request->server()->argv;
        for ($i = 0; $i < $this->skipFirstTokens; ++$i) {
            array_shift($tokens);
        }
        $this->scriptName = array_shift($tokens);
        $tokens = array_values($tokens);

        $argc = count($tokens);

        if ($argc === 1) {
            foreach (array(Runner::HELP, Runner::VERSION, Runner::BASH_COMPLETION, Runner::INSTALL) as $builtIn) {
                if ($tokens[0] === Runner::OPTION_NAME . $builtIn) {
                    $this->values[$builtIn] = true;
                    return $this;
                }
            }
        }

        $this->option = null;

        for ($index = 0; $index < $argc; ++$index) {
            $this->token = new StringValue($tokens[$index]);
            $this->processToken();
        }

        if ($this->variadicStarted) {
            $this->finishVariadic();
        }

        if (!empty($this->def->requiredArguments)) {
            foreach ($this->def->requiredArguments as $this->option) {
                throw new Exception('Missing required argument: ' . $this->option->getUsage(), Exception::ARGUMENT_REQUIRED);
            }
        }

        if (!empty($this->def->requiredOptions)) {
            foreach ($this->def->requiredOptions as $this->option) {
                throw new Exception('Option required: ' . $this->option->getUsage(), Exception::OPTION_REQUIRED);
            }
        }

        return $this;
    }
}