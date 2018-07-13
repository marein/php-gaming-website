##############################
#     Build dependencies     #
##############################
FROM composer as builder

ARG environment=development

RUN docker-php-ext-install bcmath

COPY /code/composer.json /code/composer.lock /project/
COPY /container/php-fpm/composer-install.sh /
RUN cd /project && /composer-install.sh

COPY /code /project

##############################
#       Build php-fpm        #
##############################
FROM php:7.2-fpm

ARG environment=development

WORKDIR /project

COPY /container/php-fpm/install-extensions.sh /container/php-fpm/entrypoint.sh /container/php-fpm/cache-warmup.sh /
RUN /install-extensions.sh

COPY --from=builder /project /project

RUN /cache-warmup.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
