<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Command\Runner;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestApplication;
use YaoiTests\Helper\Command\TestCommandOne;
use YaoiTests\Helper\TestRequestHelper;

class CompletionTest extends TestCase
{
    public function testCommandCompletion()
    {
        $expected = '#!/bin/bash

# Bash Completion for This is a command one for doing nothing
_the-first() {
COMPREPLY=()
local self=${COMP_WORDS[0]}
local first=${COMP_WORDS[1]}
local cur=${COMP_WORDS[COMP_CWORD]}
local prev=${COMP_WORDS[COMP_CWORD-1]}
local options=()

for opt in "--help" "--version" "--bash-completion" "--install" ; do
    if [[ $opt == $prev ]]; then
        return 0
    fi
done

if [ $COMP_CWORD = 1 ]; then
    options=("get" "delete" "create" )
fi

if [ $COMP_CWORD = 1 ]; then
    if [[ ${cur} == -* ]] ; then
        options+=("--help" "--version" "--bash-completion" "--install" )
    fi
fi

for opt in "--option-c" "--option-d" "--some-enum" ; do
    skip=
    for word in "${COMP_WORDS[@]}"; do
        if [[ $opt == $word ]]; then
            skip=1
            break;
        fi
    done
    [[ -n $skip ]] || options+=("$opt");
done

case "$prev" in
    "--option-d")
    options=()
    ;;
    "--some-enum")
    options=("one" "two" "three" )
    ;;
esac

if [[ ${cur} == -* ]] ; then
    COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
    return 0
fi

COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
}

complete -F _the-first the-first
';

        ob_start();
        Runner::create(new TestCommandOne())->run(
            TestRequestHelper::getCliRequest(Runner::OPTION_NAME . Runner::BASH_COMPLETION)
        );
        $result = ob_get_clean();
        //echo $result;
        $this->assertStringEqualsSpaceless($expected, $result);
    }


    public function testApplicationCompletion() {
        $expected = '#!/bin/bash

# Bash Completion for Test application description
_test-application() {
COMPREPLY=()
local self=${COMP_WORDS[0]}
local action=${COMP_WORDS[1]}
local cur=${COMP_WORDS[COMP_CWORD]}
local prev=${COMP_WORDS[COMP_CWORD-1]}
local options=()

for opt in "--help" "--version" "--bash-completion" "--install" ; do
    if [[ $opt == $prev ]]; then
        return 0
    fi
done

if [ $COMP_CWORD = 1 ]; then
    options=("actionOne" "actionTwo" "actionThree" )
fi

if [ $COMP_CWORD = 1 ]; then
    if [[ ${cur} == -* ]] ; then
        options+=("--help" "--version" "--bash-completion" "--install" )
    fi
fi


case "$action" in
    "actionOne")
    for opt in "--help" "--version" "--bash-completion" "--install" ; do
    if [[ $opt == $prev ]]; then
        return 0
    fi
done

if [ $COMP_CWORD = 2 ]; then
    options=()
fi
if [ $COMP_CWORD = 3 ]; then
    options=()
fi

if [ $COMP_CWORD = 2 ]; then
    if [[ ${cur} == -* ]] ; then
        options+=("--help" "--version" "--bash-completion" "--install" )
    fi
fi

for opt in "--option" ; do
    skip=
    for word in "${COMP_WORDS[@]}"; do
        if [[ $opt == $word ]]; then
            skip=1
            break;
        fi
    done
    [[ -n $skip ]] || options+=("$opt");
done

    ;;
    "actionTwo")
    for opt in "--help" "--version" "--bash-completion" "--install" ; do
    if [[ $opt == $prev ]]; then
        return 0
    fi
done

if [ $COMP_CWORD = 2 ]; then
    options=()
fi

if [ $COMP_CWORD = 2 ]; then
    if [[ ${cur} == -* ]] ; then
        options+=("--help" "--version" "--bash-completion" "--install" )
    fi
fi

for opt in "--value-option" "--bool-option" "--unified-option" ; do
    skip=
    for word in "${COMP_WORDS[@]}"; do
        if [[ $opt == $word ]]; then
            skip=1
            break;
        fi
    done
    [[ -n $skip ]] || options+=("$opt");
done

case "$prev" in
    "--value-option")
    options=()
    ;;
esac
    ;;
    "actionThree")
    for opt in "--help" "--version" "--bash-completion" "--install" ; do
    if [[ $opt == $prev ]]; then
        return 0
    fi
done

if [ $COMP_CWORD = 2 ]; then
    options=()
fi

if [ $COMP_CWORD = 2 ]; then
    if [[ ${cur} == -* ]] ; then
        options+=("--help" "--version" "--bash-completion" "--install" )
    fi
fi

    ;;
esac


if [[ ${cur} == -* ]] ; then
    COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
    return 0
fi

COMPREPLY=( $( compgen -W "${options[*]}" -- $cur) )
}

complete -F _test-application test-application';

        ob_start();
        \Yaoi\Cli\Command\Application\Runner::create(new TestApplication())->run(
            TestRequestHelper::getCliRequest(Runner::OPTION_NAME . Runner::BASH_COMPLETION)
        );
        $result = ob_get_clean();
        //echo $result;
        $this->assertStringEqualsSpaceless($expected, $result);

    }

}