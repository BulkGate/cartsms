FROM php:8.2-apache
ARG OC_VERSION
ENV OC_VERSION=${OC_VERSION:-latest}

RUN echo "Opencart  version: ${OC_VERSION}"

WORKDIR /tmp

RUN curl -L "https://github.com/opencart/opencart/archive/refs/tags/${OC_VERSION}.tar.gz" -o opencart.tar.gz

RUN tar -xzf opencart.tar.gz

RUN rm opencart.tar.gz

RUN cp -r opencart-*/upload/* /var/www/html

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
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) gd zip mysqli curl \
  && docker-php-ext-enable gd zip mysqli curl

WORKDIR /var/www/html

RUN chmod 777 system/storage/logs && \
    chmod 777 system/storage/cache && \
    cp config-dist.php config.php && \
    chmod 777 config.php && \
    cp admin/config-dist.php admin/config.php && \
    chmod 777 admin/config.php


RUN a2enmod rewrite
