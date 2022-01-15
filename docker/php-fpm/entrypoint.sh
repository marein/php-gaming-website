#!/usr/bin/env bash

set -e

if [ "$WAIT_FOR" != "" ]
then
    wait-for-tcp-server "$WAIT_FOR" 120
fi

find bin/*/onEntrypoint -print0 | xargs -0 -n 1 bash

exec "$@"
