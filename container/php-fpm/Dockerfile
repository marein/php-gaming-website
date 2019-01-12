ARG environment=development

##############################
#     Build dependencies     #
##############################
FROM gamingplatform/php-fpm:7.3-development as builder

ARG environment=development

COPY /container/php-fpm/composer-install.sh /container/php-fpm/composer-install-after-code-copy.sh /
COPY /code/composer.json /code/composer.lock /project/
RUN cd /project && /composer-install.sh

COPY /code /project
RUN cd /project && /composer-install-after-code-copy.sh

##############################
#       Build php-fpm        #
##############################
FROM gamingplatform/php-fpm:7.3-$environment

ARG environment=development

WORKDIR /project

COPY /container/php-fpm/entrypoint.sh /container/php-fpm/cache-warmup.sh /

COPY --from=builder /project /project

RUN /cache-warmup.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
