##############################
#     Build dependencies     #
##############################
FROM ubuntu:16.04 as builder

RUN apt-get update && apt-get upgrade -y \
    build-essential

COPY /code /project

RUN cd /project && make

##############################
#        Build nginx         #
##############################
FROM nginx:1.11

COPY /container/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /project/web/assets /project/web/assets
