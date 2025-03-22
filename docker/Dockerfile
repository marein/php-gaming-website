ARG environment=development

FROM spiralscout/roadrunner:2024.3 as roadrunner

##############################
#     Build dependencies     #
##############################
FROM ghcr.io/gaming-platform/docker-php-fpm:8.5-development AS builder

ARG environment=development

WORKDIR /project

RUN curl -L https://github.com/php/frankenphp/releases/download/v1.12.2/frankenphp-linux-x86_64 --output frankenphp && \
    chmod u+x frankenphp && mv frankenphp /usr/bin/frankenphp

COPY /docker/composer-install.sh /docker/composer-install-after-code-copy.sh /
COPY /composer.json /composer.lock /project/
RUN /composer-install.sh

COPY / /project
RUN /composer-install-after-code-copy.sh

##############################
#       Build php-fpm        #
##############################
FROM ghcr.io/gaming-platform/docker-php-fpm:8.5-$environment

ARG environment=development

WORKDIR /project

COPY /docker/entrypoint.sh /docker/warmup.sh /

COPY --from=builder /project /project
COPY --from=builder /usr/bin/frankenphp /usr/bin/frankenphp
COPY --from=roadrunner /usr/bin/rr /usr/bin/rr

RUN /warmup.sh

COPY /docker/fpm/${environment}.ini /etc/php/8.5/fpm/conf.d/
COPY /docker/cli/${environment}.ini /etc/php/8.5/cli/conf.d/
RUN mkdir /etc/frankenphp && \
    ln -s /etc/php/8.5/fpm/php.ini /etc/frankenphp/php.ini && \
    ln -s /etc/php/8.5/fpm/conf.d /etc/frankenphp/php.d

COPY /docker/php-rr /bin

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-http"]
