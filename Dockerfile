#############
#
# This file defines all containers which gets pushed to docker hub.
# It's a multi-stage dockerfile. There is a lot of duplication, especially
# in php images (development and production). This needs be be refactored.
#
#############

#############
#
# Build frontend as frontend-build
#
#############
FROM ubuntu:16.04 as frontend-build

RUN apt-get update && apt-get upgrade -y

# Install make
RUN apt-get install -y build-essential

COPY /code /project

RUN cd /project && make

#############
#
#     Build composer as php-build
#
#############
FROM composer as php-build

RUN docker-php-ext-install bcmath

COPY /code /project

RUN cd /project && composer install --optimize-autoloader --no-dev --classmap-authoritative

#############
#
#     Build php-cli
#
#############
FROM php:7.2-cli as php-cli

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install bcmath

COPY --from=php-build /project /project
RUN rm -rf /project/var
COPY /container/php-production-entrypoint.sh /entrypoint.sh
RUN chmod u+x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

#############
#
#     Build php-fpm
#
#############
FROM php:7.2-fpm as php-fpm

RUN docker-php-ext-install opcache
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install bcmath

COPY --from=php-build /project /project
RUN rm -rf /project/var
COPY /container/php-production-entrypoint.sh /entrypoint.sh
RUN chmod u+x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

#############
#
#     Build nginx
#
#############
FROM nginx:1.11 as nginx

COPY /container/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=frontend-build /project/web/main.css /project/web/main.css
COPY --from=frontend-build /project/web/loading-indicator.svg /project/web/loading-indicator.svg
COPY --from=frontend-build /project/web/main.js /project/web/main.js

#############
#
#     Build nchan
#
#############
FROM meroje/alpine-nchan:standard as nchan

COPY /container/nchan/default.conf /etc/nginx/conf.d/default.conf

RUN sed -i "1iload_module 'modules/ngx_nchan_module.so';" /etc/nginx/nginx.conf

#############
#
#     Build mysql
#
#############
FROM mysql:8.0 as mysql

COPY /container/mysql/my.cnf /etc/mysql/conf.d/my.cnf

#############
#
#     Build redis
#
#############
FROM redis:3.0 as redis

#############
#
#     Build rabbit-mq
#
#############
FROM rabbitmq:3.6-management as rabbit-mq
