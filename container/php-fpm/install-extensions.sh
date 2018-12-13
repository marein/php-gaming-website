#!/usr/bin/env bash

docker-php-ext-install pdo_mysql
docker-php-ext-install bcmath

if [ "$environment" = "development" ]
then
    apk add --no-cache inotify-tools
    apk add --no-cache $PHPIZE_DEPS
    # todo: Switch to xdebug if it's released for php 7.3
    pecl install xdebug-beta
    docker-php-ext-enable xdebug
else
    docker-php-ext-install opcache
fi

rm -rf /var/cache/apk/*
