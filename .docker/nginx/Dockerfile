FROM nginx:latest

ENV TZ=Europe/London
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN set -eux; \
    apt-get update && apt-get -y upgrade; \
    mkdir /etc/nginx/ssl && cd /etc/nginx/ssl; \
    apt-get -y install openssl; \
    openssl req -x509 -nodes -new -sha256 -days 1024 -newkey rsa:2048 -keyout localhost.key -out localhost.pem -subj "/C=GB/CN=localhost"; \
    openssl x509 -outform pem -in localhost.pem -out localhost.crt;
