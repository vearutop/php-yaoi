<?php

namespace Yaoi\Cli\Command;

use Yaoi\BaseClass;
use Yaoi\Command\Exception;
use Yaoi\Cli\Response;
use Yaoi\Command;
use Yaoi\Io\Content\SubContent;
use Yaoi\Io\Request;
use Yaoi\Cli\View\Table;
use Yaoi\Io\Content\Heading;
use Yaoi\String\Utils;

class Runner extends BaseClass implements \Yaoi\Command\RunnerContract
{
    const OPTION_NAME = '--';
    const OPTION_SHORT = '-';

    const HELP = 'help';
    const VERSION = 'version';
    const BASH_COMPLETION = 'bash-completion';
    const INSTALL = 'install';

    const GROUP_MISC = 'Misc';
    const GROUP_DEFAULT = 'Options';

    /** @var Command */
    protected $command;

    /** @var \Yaoi\Command\Option[]  */
    protected $optionsArray;

    protected $commandName;
    protected $commandDescription;
    protected $commandVersion;

    public function __construct(Command $command) {
        $this->command = $command;
        $definition = $command->definition();
        $this->commandName = $definition->name;
        $this->commandVersion = $definition->version;
        $this->commandDescription = $definition->description;
        $this->optionsArray = $this->command->optionsArray();
        $this->response = new Response();
        $command->setResponse($this->response);
    }

    protected $showHelp;
    protected $showVersion;
    protected $showBashCompletion;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var RequestReader */
    protected $reader;

    /**
     * @var int Skips specified count of tokens at `argv` head, for embedding in application runner
     */
    protected $skipFirstTokens = 0;


    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }

        $this->request = $request;

        try {
            $this->reader = new RequestReader();
            $this->reader->skipFirstTokens = $this->skipFirstTokens;
            $this->reader->read($request, $this->command->optionsArray());
        } catch (Exception $exception) {
            $this->response->error($exception->getMessage());
            $this->response->addContent('Use --help to show information.');
            return $this;
        }

        if (!empty($this->reader->values[self::HELP])) {
            $this->showHelp();
            return $this;
        } elseif (!empty($this->reader->values[self::VERSION])) {
            $this->showVersion();
            return $this;
        } elseif (!empty($this->reader->values[self::BASH_COMPLETION])) {
            $this->showBashCompletion();
            return $this;
        } elseif (!empty($this->reader->values[self::INSTALL])) {
            $this->install();
            return $this;
        } else {
            foreach ($this->reader->values as $name => $value) {
                $this->command->$name = $value;
            }
            $this->command->performAction();
            return $this;
        }
    }


    public function showVersion()
    {
        if ($this->commandName) {

            $versionText = '';
            if ($this->commandVersion) {
                $versionText .= $this->commandVersion . ' ';
            }

            $versionText .= $this->commandName;
            $this->response->addContent(new Heading($versionText));
        }
        if ($this->commandDescription) {
            $this->response->addContent(new Heading($this->commandDescription));
        }
    }

    public function showBashCompletion()
    {
        $completion = new Completion($this->command);
        $completion->render();
    }

    public function showHelp()
    {
        $this->showVersion();

        try {
            $def = new PrepareDefinition($this->optionsArray);
        } catch (Exception $exception) {
            $this->response->error('Command definition error: ' . $exception->getMessage());
            return;
        }

        $def->initOptions();


        $this->response->addContent(new Heading('Usage: '));
        $this->response->addContent(new SubContent($this->commandName . $def->usage));

        if ($def->argumentsDescription) {
            $this->response->addContent(new SubContent(Table::create(new \ArrayIterator($def->argumentsDescription))));
        }

        if ($def->optionsDescription) {
            foreach ($def->optionsDescription as $group => $descriptions) {
                $this->response->addContent(new Heading($group . ": "));
                $this->response->addContent(new SubContent(Table::create(new \ArrayIterator($descriptions))));
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function install()
    {
        $this->response->addContent('Installing');

        $request = Request::createAuto();
        if (!$request->isCli()) {
            $this->response->error('CLI mode required');
            return;
        }

        $scriptFilename = realpath($request->server()->SCRIPT_NAME);
        $basename = basename($scriptFilename);

        ob_start();
        $this->showBashCompletion();
        $completion = ob_get_clean();

        $completionDirs = array(
            '/usr/local/etc/bash_completion.d/',
            '/etc/bash_completion.d/',
        );

        $completionDir = null;
        foreach ($completionDirs as $dir) {
            if (file_exists($dir)) {
                $completionDir = $dir;
                break;
            }
        }

        if (null === $completionDir) {
            $this->response->error('bash_completion.d not found');
            return;
        }

        $result = file_put_contents($completionDir . $basename, $completion)
            && chmod($completionDir . $basename, 0755);

        if (!$result) {
            $this->response->error('Unable to save bash completion');
            return;
        }

        $scriptFilenameInstall = '/usr/local/bin/' . $basename;
        $cmd = 'ln -s ' . $scriptFilename . ' ' . $scriptFilenameInstall;
        $this->response->addContent($cmd);
        system($cmd, $result);
        if ($result) {
            $this->response->error('Unable to create symlink to ' . $scriptFilenameInstall);
        }
    }

    public static function getPublicName($name) {
        return Utils::fromCamelCase($name, '-');
    }

}