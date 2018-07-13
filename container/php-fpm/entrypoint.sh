#!/usr/bin/env bash

if [ "$WAIT_FOR" != "" ]
then
    IFS=","
    for v in $WAIT_FOR
    do
        /project/bin/waitForIt $v --timeout=120
    done
fi

/project/bin/console doctrine:database:create --connection=chat --if-not-exists >/dev/null 2>/dev/null
/project/bin/console doctrine:database:create --connection=connect_four --if-not-exists >/dev/null 2>/dev/null
/project/bin/console doctrine:database:create --connection=identity --if-not-exists >/dev/null 2>/dev/null
/project/bin/console doctrine:migrations:migrate --configuration=/project/config/chat/migrations.yml --db=chat --no-interaction >/dev/null 2>/dev/null
/project/bin/console doctrine:migrations:migrate --configuration=/project/config/connect-four/migrations.yml --db=connect_four --no-interaction >/dev/null 2>/dev/null
/project/bin/console doctrine:migrations:migrate --configuration=/project/config/identity/migrations.yml --db=identity --no-interaction >/dev/null 2>/dev/null

exec "$@"
