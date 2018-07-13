FROM meroje/alpine-nchan:standard

COPY /container/nchan/default.conf /etc/nginx/conf.d/default.conf

RUN sed -i "1iload_module 'modules/ngx_nchan_module.so';" /etc/nginx/nginx.conf
