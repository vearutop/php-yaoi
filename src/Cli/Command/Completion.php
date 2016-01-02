<?php

namespace Yaoi\Cli\Command;

use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\View\Hardcoded;

class Completion extends Hardcoded
{
    protected $command;

    public function __construct(Command $command) {
        $this->command = $command;
    }

    /** @var  PrepareDefinition */
    private $prep;
    private $optionsValues = array();
    private $argumentsShift;
    private $requiredArguments = array();
    private $optionsStrings = array();

    private function prepareRequiredArguments()
    {
        $this->requiredArguments = array(
            $this->argumentsShift => '',
        );

        foreach ($this->prep->requiredArguments as $index => $argument) {
            $index += $this->argumentsShift;

            if ($argument->type === Option::TYPE_ENUM) {
                $this->requiredArguments[$index] = '';
                foreach ($argument->values as $value) {
                    $this->requiredArguments[$index] .= '"' . $value . '" ';
                }
            } else {
                $this->requiredArguments[$index] = '';
            }
        }
    }

    private function prepareOptions()
    {
        $this->optionsStrings = array(
            Runner::GROUP_MISC => '',
            Runner::GROUP_DEFAULT => '',
        );

        $this->optionsValues = array();

        foreach ($this->prep->optionsArray as $option) {
            if ($option instanceof Option && $option->isUnnamed) {
                continue;
            }

            $group = $option->group === Runner::GROUP_MISC
                ? Runner::GROUP_MISC
                : Runner::GROUP_DEFAULT;

            $this->optionsStrings [$group] .= '"' . Runner::OPTION_NAME . $option->getPublicName() . '" ';

            if ($option->type === Option::TYPE_VALUE) {
                $this->optionsValues [Runner::OPTION_NAME . $option->getPublicName()] = '';
            } elseif ($option->type === Option::TYPE_ENUM) {
                $this->optionsValues [Runner::OPTION_NAME . $option->getPublicName()] = '"' . implode('" "', $option->values) . '" ';
            }
        }

    }

    protected function renderDefinition(Command\Definition $definition, $argumentsShift = 1)
    {
        $this->prep = new PrepareDefinition((array)$definition->options);
        $this->argumentsShift = $argumentsShift;

        $this->prepareRequiredArguments();
        $this->prepareOptions();

        $this->echoBash();
    }


    private function echoBash() {
        ?>
for opt in <?php echo $this->optionsStrings[Runner::GROUP_MISC] ?>; do
    if [[ $opt == $prev ]]; then
        return 0
    fi
done

<?php foreach ($this->requiredArguments as $index => $values) { ?>
if [ $COMP_CWORD = <?= $index ?> ]; then
    options=(<?php echo $values ?>)
fi
<?php } ?>

if [ $COMP_CWORD = <?php echo $this->argumentsShift ?> ]; then
    if [[ ${cur} == -* ]] ; then
        options+=(<?php echo $this->optionsStrings[Runner::GROUP_MISC] ?>)
    fi
fi

<?php if ($this->optionsStrings[Runner::GROUP_DEFAULT]) { ?>
for opt in <?php echo $this->optionsStrings[Runner::GROUP_DEFAULT] ?>; do
    skip=
    for word in "${COMP_WORDS[@]}"; do
        if [[ $opt == $word ]]; then
            skip=1
            break;
        fi
    done
    [[ -n $skip ]] || options+=("$opt");
done

<?php
    }
        if (!empty($this->optionsValues)) { ?>
case "$prev" in
<?php foreach ($this->optionsValues as $name => $values) { ?>
    "<?php echo $name ?>")
    options=(<?php echo $values; ?>)
    ;;
<?php } ?>
esac
<?php }

    }


    public function render()
    {
        $def = $this->command->definition();
?>
#!/bin/bash

# Bash Completion for <?=$def->description?>

_<?= $def->name ?>() {
COMPREPLY=()
local self=${COMP_WORDS[0]}
local first=${COMP_WORDS[1]}
local cur=${COMP_WORDS[COMP_CWORD]}
local prev=${COMP_WORDS[COMP_CWORD-1]}
local options=()

<?php $this->renderDefinition($def); ?>

if [[ ${cur} == -* ]] ; then
    COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
    return 0
fi

COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
}

complete -F _<?= $def->name ?> <?= $def->name ?>

<?php
}

}