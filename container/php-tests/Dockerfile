FROM php:7.2-cli

RUN docker-php-ext-install pdo_mysql

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Must have for enqueue/amqp-lib
RUN docker-php-ext-install bcmath
