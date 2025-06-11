FROM php:8.2-fpm-alpine

RUN apk update && apk add \
    build-base \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

WORKDIR /var/www

USER www

COPY --chown=www:www . /var/www

EXPOSE 9000
CMD ["php-fpm"]
