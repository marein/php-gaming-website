#!/usr/bin/env bash

set -e

if [ "${PROXYSQL_CONFIG}" != "" ]
then
    echo "${PROXYSQL_CONFIG}" > /etc/proxysql.cnf
fi

exec "$@"
