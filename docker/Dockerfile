ARG PHP_VERSION

FROM php:${PHP_VERSION}-apache

RUN apt-get update && apt-get install sudo unzip

RUN curl -sSfLo /usr/local/bin/composer https://getcomposer.org/download/1.10.26/composer.phar && \
    sha256sum /usr/local/bin/composer | grep cbfe1f85276c57abe464d934503d935aa213494ac286275c8dfabfa91e3dbdc4 && \
	chmod +x /usr/local/bin/composer

COPY --from=mlocati/php-extension-installer:2.1.49 /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    bcmath \
    gd \
    intl \
    pdo_mysql \
    soap \
    xsl \
    zip

COPY --chmod=0755 docker/entrypoint.sh /entrypoint.sh

ENTRYPOINT /entrypoint.sh
