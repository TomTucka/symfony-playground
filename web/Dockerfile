FROM nginx:stable-alpine

WORKDIR /var/www

COPY default.conf /etc/nginx/conf.d/default.conf
COPY mime.types /etc/nginx/mime.types
COPY index.html web/index.html
