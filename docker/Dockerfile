ARG ALPINE_VERSION
FROM alpine:${ALPINE_VERSION}

ARG PHP_VERSION
RUN apk add --no-cache \
    bash \
    gcc \
    libsodium-dev \
    make \
    musl-dev \
    shadow \
    sudo \
    ${PHP_VERSION}-apache2 \
    ${PHP_VERSION}-bcmath \
    ${PHP_VERSION}-cli \
    ${PHP_VERSION}-ctype \
    ${PHP_VERSION}-curl \
    ${PHP_VERSION}-dev \
    ${PHP_VERSION}-dom \
    ${PHP_VERSION}-fileinfo \
    ${PHP_VERSION}-gd \
    ${PHP_VERSION}-iconv \
    ${PHP_VERSION}-intl \
    ${PHP_VERSION}-json \
    ${PHP_VERSION}-mbstring \
    ${PHP_VERSION}-opcache \
    ${PHP_VERSION}-openssl \
    ${PHP_VERSION}-pdo_mysql \
    ${PHP_VERSION}-pear \
    ${PHP_VERSION}-phar \
    ${PHP_VERSION}-session \
    ${PHP_VERSION}-simplexml \
    ${PHP_VERSION}-soap \
    ${PHP_VERSION}-sockets \
    ${PHP_VERSION}-tokenizer \
    ${PHP_VERSION}-xml \
    ${PHP_VERSION}-xmlwriter \
    ${PHP_VERSION}-xsl \
    ${PHP_VERSION}-zip

RUN pecl install libsodium
RUN echo 'extension=sodium.so' > /etc/${PHP_VERSION}/conf.d/00_sodium.ini

RUN rm -rf /var/www/localhost/htdocs && ln -sfn ../../../data/www/pub /var/www/localhost/htdocs

COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

ENTRYPOINT /entrypoint.sh
