ARG environment=development

##############################
#     Build dependencies     #
##############################
FROM ghcr.io/gaming-platform/docker-php-fpm:8.3-development as builder

ARG environment=development

WORKDIR /project

COPY /docker/php-fpm/composer-install.sh /docker/php-fpm/composer-install-after-code-copy.sh /
COPY /composer.json /composer.lock /project/
RUN /composer-install.sh

COPY / /project
RUN /composer-install-after-code-copy.sh

##############################
#       Build php-fpm        #
##############################
FROM ghcr.io/gaming-platform/docker-php-fpm:8.3-$environment

ARG environment=development

WORKDIR /project

COPY /docker/php-fpm/entrypoint.sh /docker/php-fpm/warmup.sh /

COPY --from=builder /project /project

RUN /warmup.sh

COPY /docker/php-fpm/${environment}.ini /etc/php/8.3/fpm/conf.d/

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-http"]
