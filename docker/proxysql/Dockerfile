FROM proxysql/proxysql:2.4.8

RUN apt-get update && apt-get upgrade -y && apt-get install -y net-tools

COPY /docker/proxysql/entrypoint.sh /

ENTRYPOINT ["/entrypoint.sh"]
CMD ["proxysql", "-f", "--idle-threads", "-D", "/var/lib/proxysql"]
