ARG environment=development

##############################
#     Build dependencies     #
##############################
FROM gamingplatform/php-fpm:8.1-development as builder

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
FROM gamingplatform/php-fpm:8.1-$environment

ARG environment=development

WORKDIR /project

COPY /docker/php-fpm/entrypoint.sh /docker/php-fpm/cache-warmup.sh /

COPY --from=builder /project /project

RUN /cache-warmup.sh

COPY /docker/php-fpm/${environment}.ini /etc/php/8.0/fpm/conf.d/

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-http"]
