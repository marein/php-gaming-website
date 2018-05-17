#!/usr/bin/env bash

# Wait for the database coming up.
# We should use something like https://github.com/eficode/wait-for instead of sleep.
sleep 15

rm -rf /project/var/*

/project/bin/console doctrine:database:create --connection=chat --if-not-exists
/project/bin/console doctrine:database:create --connection=connect_four --if-not-exists
/project/bin/console doctrine:database:create --connection=identity --if-not-exists
/project/bin/console doctrine:migrations:migrate --configuration=/project/app/config/chat/migrations.yml --db=chat -n
/project/bin/console doctrine:migrations:migrate --configuration=/project/app/config/connect-four/migrations.yml --db=connect_four -n
/project/bin/console doctrine:migrations:migrate --configuration=/project/app/config/identity/migrations.yml --db=identity -n

exec "$@"
