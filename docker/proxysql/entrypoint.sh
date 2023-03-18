#!/usr/bin/env bash

set -e

if [ "${PROXYSQL_CNF}" != "" ]
then
    echo "${PROXYSQL_CNF}" > /etc/proxysql.cnf
fi

exec "$@"
