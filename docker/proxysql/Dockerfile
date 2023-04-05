FROM proxysql/proxysql:2.4.8

COPY /docker/proxysql/entrypoint.sh /

ENTRYPOINT ["/entrypoint.sh"]
CMD ["proxysql", "-f", "--idle-threads", "-D", "/var/lib/proxysql"]
