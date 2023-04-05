#!/usr/bin/env bash

envsubst "$(printf '${%s}' ${!NCHAN_*})" \
    < "/etc/nginx/sites-template/default.conf" \
    > "/etc/nginx/conf.d/default.conf"

exec "$@"
