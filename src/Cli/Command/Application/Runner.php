<?php

namespace Yaoi\Cli\Command\Application;


use Yaoi\Cli\Command\RequestReader;
use Yaoi\Command;
use Yaoi\Command\Application;
use Yaoi\Io\Request;

class Runner extends \Yaoi\Cli\Command\Runner
{

    /** @var Application */
    protected $command;

    public function __construct(Command $command)
    {
        parent::__construct($command);
    }

    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }

        $this->request = $request;

        try {
            if (!$this->command instanceof Application) {
                throw new Command\Exception('Application required', Command\Exception::INVALID_ARGUMENT);
            }

            $this->reader = new RequestReader();
            $this->reader->read($request, $this->command->optionsArray());
        } catch (Command\Exception $exception) {
            if (empty($this->reader->values['action'])) { // TODO symbolize 'action' literal
                $this->response->error($exception->getMessage());
                $this->response->addContent('Use --help to show information.');
                return $this;
            }
        }

        foreach ($this->reader->values as $name => $value) {
            $this->command->$name = $value;
        }


        if (isset($this->command->action)) {
            $action = $this->command->action;
            $commandDefinition = $this->command->definition()->actions[$action];
            $command = new $commandDefinition->commandClass;

            $runner = new \Yaoi\Cli\Command\Runner($command);
            $runner->commandName = $this->commandName . ' ' . $action;
            $runner->commandVersion = $this->commandVersion;
            $runner->commandDescription = $this->commandDescription
                . ($runner->commandDescription ? PHP_EOL . $runner->commandDescription : '');
            $runner->skipFirstTokens = 1;
            $runner->run($request);
        } elseif (!empty($this->reader->values[self::HELP])) {
            $this->showHelp();
        } elseif (!empty($this->reader->values[self::VERSION])) {
            $this->showVersion();
        } elseif (!empty($this->reader->values[self::BASH_COMPLETION])) {
            $this->showBashCompletion();
        } // @codeCoverageIgnoreStart
        elseif (!empty($this->reader->values[self::INSTALL])) {
            $this->install();
        }
        // @codeCoverageIgnoreEnd

        return $this;
    }


    public function showBashCompletion()
    {
        $completion = new Completion($this->command);
        $completion->render();
    }


}