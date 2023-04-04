FROM safesecurity/nginx-nchan:1.2.15

ENV NCHAN_SHARED_MEMORY_SIZE=512M \
    NCHAN_HTTP_KEEPALIVE_REQUESTS=10000

COPY /docker/nchan/entrypoint.sh /
COPY /docker/nchan/default.conf /etc/nginx/sites-template/

ENTRYPOINT ["/entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
