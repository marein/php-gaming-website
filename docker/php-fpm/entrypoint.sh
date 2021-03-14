#!/usr/bin/env bash

set -e

if [ "$WAIT_FOR" != "" ]
then
    wait-for-tcp-server "$WAIT_FOR" 120
fi

bin/console doctrine:database:create \
    --connection=chat \
    --if-not-exists
bin/console doctrine:database:create \
    --connection=connect_four \
    --if-not-exists
bin/console doctrine:database:create \
    --connection=identity \
    --if-not-exists
bin/console doctrine:migrations:migrate \
    --configuration=config/chat/migrations.yml \
    --db=chat \
    --no-interaction \
    --all-or-nothing \
    --allow-no-migration
bin/console doctrine:migrations:migrate \
    --configuration=config/connect-four/migrations.yml \
    --db=connect_four \
    --no-interaction \
    --all-or-nothing \
    --allow-no-migration
bin/console doctrine:migrations:migrate \
    --configuration=config/identity/migrations.yml \
    --db=identity \
    --no-interaction \
    --all-or-nothing \
    --allow-no-migration

exec "$@"
