#!/usr/bin/env bash

docker-php-ext-install pdo_mysql
docker-php-ext-install bcmath

if [ "$environment" = "development" ]
then
    pecl install xdebug
    docker-php-ext-enable xdebug
else
    docker-php-ext-install opcache
fi

rm -rf /var/cache/apk/*
