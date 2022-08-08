#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$DIR" )"

cd "$PROJECT_ROOT"
set -x

function phpcs {
    php -d memory_limit=-1 vendor/bin/phpcs --standard=phpcs.xml --extensions=php --encoding=utf-8 -sp "$@"
}

function phpcsFixer {
    php -d memory_limit=-1 vendor/bin/phpcbf --standard=phpcs.xml --extensions=php --encoding=utf-8 -sp "$@"
}

if [[ "$#" -gt 0 ]]; then
    if [[ "$1" == "--fix" || $1 == "-f" ]]; then
        shift
        if [[ "$#" -gt 0 ]]; then
            phpcsFixer "$@"
        else
            phpcsFixer src tests
        fi
    else
        phpcs "$@"
    fi
else
    phpcs -n src tests
fi


