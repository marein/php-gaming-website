#!/usr/bin/env bash

set -e

echo -e "datadir=\"/var/run/proxysql\"\n" > /etc/proxysql.cnf

echo -e "admin_variables:\n{" >> /etc/proxysql.cnf
for adminVariableName in ${!PROXYSQL_ADMIN_VARIABLES_*}
do
    declare -n adminVariable=${adminVariableName}
    echo "  ${adminVariable}" >> /etc/proxysql.cnf
done
echo "}" >> /etc/proxysql.cnf

echo -e "mysql_variables:\n{" >> /etc/proxysql.cnf
for mysqlVariableName in ${!PROXYSQL_MYSQL_VARIABLES_*}
do
    declare -n mysqlVariable=${mysqlVariableName}
    echo "  ${mysqlVariable}" >> /etc/proxysql.cnf
done
echo "}" >> /etc/proxysql.cnf

echo -e "mysql_servers:\n(" >> /etc/proxysql.cnf
for mysqlServerName in ${!PROXYSQL_MYSQL_SERVERS_*}
do
    declare -n mysqlServer=${mysqlServerName}
    echo "  {${mysqlServer}}," >> /etc/proxysql.cnf
done
echo ")" >> /etc/proxysql.cnf

echo -e "mysql_users:\n(" >> /etc/proxysql.cnf
for mysqlUserName in ${!PROXYSQL_MYSQL_USERS_*}
do
    declare -n mysqlUser=${mysqlUserName}
    echo "  {${mysqlUser}}," >> /etc/proxysql.cnf
done
echo ")" >> /etc/proxysql.cnf

echo -e "mysql_query_rules:\n(" >> /etc/proxysql.cnf
queryRuleId=1
for queryRuleName in ${!PROXYSQL_QUERY_RULES_*}
do
    declare -n queryRule=${queryRuleName}
    echo "  {rule_id=${queryRuleId},apply=1,${queryRule}}," >> /etc/proxysql.cnf
    queryRuleId=$((queryRuleId+1))
done
echo ")" >> /etc/proxysql.cnf

exec "$@"
