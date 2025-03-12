FROM php:8.2-apache
ARG XDEBUG_VERSION=3.4.0
ARG OC_VERSION=latest

LABEL org.opencontainers.image.source=https://github.com/bulkgate/cartsms
LABEL opencart_version=${OC_VERSION}

ENV OC_VERSION=${OC_VERSION}

RUN echo "Opencart  version: ${OC_VERSION}"

WORKDIR /tmp

RUN curl -fL "https://github.com/opencart/opencart/archive/refs/tags/${OC_VERSION}.tar.gz" -o opencart.tar.gz && \
    curl -sS https://raw.githubusercontent.com/composer/getcomposer.org/f3108f64b4e1c1ce6eb462b159956461592b3e3e/web/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN tar -xzf opencart.tar.gz

RUN rm opencart.tar.gz

USER www-data:www-data

RUN cp -r opencart-*/upload/* /var/www/html

WORKDIR /var/www/html

RUN cp config-dist.php config.php && \
    cp admin/config-dist.php admin/config.php

USER root

VOLUME /var/www/html

RUN apt-get update \
  && apt-get install -y \
             wait-for-it \
             unzip \
             libfreetype6-dev \
             libjpeg62-turbo-dev \
             libpng-dev \
             libzip-dev \
             libcurl3-dev \
             libwebp-dev \
  && pecl install xdebug-${XDEBUG_VERSION} \
  && docker-php-ext-enable xdebug \
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) gd zip mysqli curl \
  && docker-php-ext-enable gd zip mysqli curl

RUN a2enmod rewrite

COPY --chmod=777 entrypoint.sh /usr/sbin

ENTRYPOINT ["entrypoint.sh"]
