FROM php:7.1-cli

RUN docker-php-ext-install pdo_mysql

# Must have for enqueue/amqp-lib
RUN docker-php-ext-install bcmath
