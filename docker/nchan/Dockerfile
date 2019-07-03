FROM meroje/alpine-nchan:1.1.15

COPY /docker/nchan/default.conf /etc/nginx/conf.d/default.conf

RUN sed -i "1iload_module 'modules/ngx_nchan_module.so';" /etc/nginx/nginx.conf
