##############################
#     Build dependencies     #
##############################
FROM alpine:3.8 as builder

RUN apk add --no-cache --update make

COPY /code /project

RUN cd /project && make

##############################
#        Build nginx         #
##############################
FROM nginx:1.15-alpine

COPY /container/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=builder /project/web/assets /project/web/assets
