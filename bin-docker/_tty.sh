#!/usr/bin/env bash

if [ -t 1 ] && [ -t 0 ]; then
	DC_INTERACTIVITY=""
else
	DC_INTERACTIVITY="-T"
fi

function docker_run {
	if [ -t 1 ] && [ -t 0 ]; then
		docker run --rm --interactive --tty=true "$@"
	else
		docker run --rm --interactive --tty=false "$@"
	fi
}
