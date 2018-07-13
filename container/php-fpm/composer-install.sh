#!/usr/bin/env bash

if [ "$environment" = "development" ]
then
    composer install
else
    composer install --optimize-autoloader --no-dev
#    composer install --optimize-autoloader --no-dev --classmap-authoritative
fi
