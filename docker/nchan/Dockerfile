FROM safesecurity/nginx-nchan:1.2.15

ENV NCHAN_SHARED_MEMORY_SIZE=256M \
    NCHAN_MESSAGE_BUFFER_LENGTH=10 \
    NCHAN_MESSAGE_TIMEOUT=10s \
    NCHAN_HTTP_KEEPALIVE_REQUESTS=10000

COPY /docker/nchan/entrypoint.sh /
COPY /docker/nchan/default.conf /etc/nginx/sites-template/

ENTRYPOINT ["/entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
