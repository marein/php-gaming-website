#!/usr/bin/env bash

if [ "$environment" = "development" ]
then
    bin/console cache:warmup
else
    bin/console cache:warmup --env=prod
fi
