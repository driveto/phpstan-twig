#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$DIR" )"

cd "$PROJECT_ROOT"
set -x

php -d memory_limit=-1 vendor/bin/phpunit
