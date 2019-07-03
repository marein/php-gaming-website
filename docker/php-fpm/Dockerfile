ARG environment=development

##############################
#     Build dependencies     #
##############################
FROM gamingplatform/php-fpm:7.3-development as builder

ARG environment=development

WORKDIR /project

COPY /docker/php-fpm/composer-install.sh /docker/php-fpm/composer-install-after-code-copy.sh /
COPY /composer.json /composer.lock /project/
RUN cd /project && /composer-install.sh

COPY / /project
RUN cd /project && /composer-install-after-code-copy.sh

##############################
#       Build php-fpm        #
##############################
FROM gamingplatform/php-fpm:7.3-$environment

ARG environment=development

WORKDIR /project

COPY /docker/php-fpm/entrypoint.sh /docker/php-fpm/cache-warmup.sh /

COPY --from=builder /project /project

RUN /cache-warmup.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
