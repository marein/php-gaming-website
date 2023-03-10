#!/usr/bin/env bash

set -e

bin/console cache:warmup --env="${APP_ENVIRONMENT}"

if [ "${APP_WAIT_FOR}" != "" ]
then
    wait-for-tcp-server "${APP_WAIT_FOR}" 120
fi

find bin/*/onEntrypoint -print0 | xargs -0 -n 1 bash

exec "$@"
