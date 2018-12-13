##############################
#     Build dependencies     #
##############################
FROM composer as builder

ARG environment=development

RUN docker-php-ext-install bcmath

COPY /container/php-fpm/composer-install.sh /container/php-fpm/composer-install-after-code-copy.sh /
COPY /code/composer.json /code/composer.lock /project/
RUN cd /project && /composer-install.sh

COPY /code /project
RUN cd /project && /composer-install-after-code-copy.sh

##############################
#       Build php-fpm        #
##############################
FROM php:7.3-fpm-alpine

ARG environment=development

WORKDIR /project

RUN apk add --no-cache --update bash

COPY /container/php-fpm/install-extensions.sh /container/php-fpm/entrypoint.sh /container/php-fpm/cache-warmup.sh /
RUN /install-extensions.sh

COPY --from=builder /project /project

RUN /cache-warmup.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
