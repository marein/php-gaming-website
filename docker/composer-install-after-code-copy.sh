#!/usr/bin/env bash

if [ "$environment" = "production" ]
then
    composer install --no-dev --optimize-autoloader --classmap-authoritative
fi
