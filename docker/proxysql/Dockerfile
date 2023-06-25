FROM proxysql/proxysql:2.5.3

COPY /docker/proxysql/entrypoint.sh /

ENTRYPOINT ["/entrypoint.sh"]
CMD ["proxysql", "-f", "--idle-threads", "-D", "/var/lib/proxysql"]
