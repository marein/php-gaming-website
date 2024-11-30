ARG environment=development

##############################
#     Build dependencies     #
##############################
FROM ghcr.io/gaming-platform/docker-php-fpm:8.4-development as builder

ARG environment=development

WORKDIR /project

COPY /docker/composer-install.sh /docker/composer-install-after-code-copy.sh /
COPY /composer.json /composer.lock /project/
RUN /composer-install.sh

COPY / /project
RUN /composer-install-after-code-copy.sh

##############################
#       Build php-fpm        #
##############################
FROM ghcr.io/gaming-platform/docker-php-fpm:8.4-$environment

ARG environment=development

WORKDIR /project

COPY /docker/entrypoint.sh /docker/warmup.sh /

COPY --from=builder /project /project

RUN /warmup.sh

COPY /docker/${environment}.ini /etc/php/8.4/fpm/conf.d/

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-http"]
