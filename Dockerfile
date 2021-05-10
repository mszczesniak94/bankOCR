FROM php:7.4.5-fpm-alpine AS base-env

FROM base-env AS base-image

ARG xdebug=true

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer

RUN apk add --no-cache autoconf g++ make git bash
RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

RUN pecl install apcu && docker-php-ext-enable apcu

#optional xdebug disable by - docker-compose build --build-arg xdebug=false
RUN if [ "$xdebug" = "true" ] ; then pecl install xdebug && docker-php-ext-enable xdebug \
        && echo 'xdebug.remote_port=9001' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
        && echo 'xdebug.remote_enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
        && echo 'xdebug.remote_connect_back=0' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
        && echo 'xdebug.remote_host=docker.for.mac.localhost' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; fi

RUN echo 'date.timezone=Europe/Warsaw' >> /usr/local/etc/php/php.ini

#cache
RUN echo 'realpath_cache_ttl=120' >> /usr/local/etc/php/php.ini \
    && echo 'realpath_cache_size=4096K' >> /usr/local/etc/php/php.ini

#opcache
RUN echo 'opcache.memory_consumption=256' >> /usr/local/etc/php/php.ini \
    && echo 'opcache.max_accelerated_files=20000' >> /usr/local/etc/php/php.ini

FROM base-image AS base-app

WORKDIR /application
COPY . .
CMD composer install
