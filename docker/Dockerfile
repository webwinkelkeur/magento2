FROM alpine:3.16

RUN apk add --no-cache \
    bash \
    gcc \
    libsodium-dev \
    make \
    musl-dev \
    shadow \
    sudo \
    php8-apache2 \
    php8-bcmath \
    php8-cli \
    php8-ctype \
    php8-curl \
    php8-dev \
    php8-dom \
    php8-fileinfo \
    php8-gd \
    php8-iconv \
    php8-intl \
    php8-json \
    php8-mbstring \
    php8-opcache \
    php8-openssl \
    php8-pdo_mysql \
    php8-pear \
    php8-phar \
    php8-session \
    php8-simplexml \
    php8-soap \
    php8-sockets \
    php8-tokenizer \
    php8-xml \
    php8-xmlwriter \
    php8-xsl \
    php8-zip

RUN pecl install libsodium
RUN echo 'extension=sodium.so' > /etc/php8/conf.d/00_sodium.ini

RUN rm -rf /var/www/localhost/htdocs && ln -sfn ../../../data/www/pub /var/www/localhost/htdocs

COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

ENTRYPOINT /entrypoint.sh
