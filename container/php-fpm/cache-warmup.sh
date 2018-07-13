#!/usr/bin/env bash

if [ "$environment" = "development" ]
then
    /project/bin/console cache:warmup
else
    /project/bin/console cache:warmup --env=prod
fi
