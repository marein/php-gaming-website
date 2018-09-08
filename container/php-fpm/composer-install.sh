#!/usr/bin/env bash

if [ "$environment" = "development" ]
then
    composer install
else
    # Call install without --optimize-autoloader --classmap-authoritative.
    # Those options will be enabled after the whole code is copied.
    # Look at composer-install-after-code-copy.sh.
    composer install --no-dev
fi
