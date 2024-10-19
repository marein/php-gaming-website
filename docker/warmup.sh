#!/usr/bin/env bash

set -e

# Although environments variables are fake during docker build,
# make them available for the warmup commands.
set -a && source /project/.env && set +a

if [ "$environment" = "development" ]
then
    bin/console cache:warmup
    bin/console importmap:install
else
    bin/console cache:warmup --env=prod
    bin/console importmap:install --env=prod
    bin/console asset-map:compile --env=prod
fi

chown -R www-data:www-data var
