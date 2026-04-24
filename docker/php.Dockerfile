FROM php:8.4-fpm

ARG USER_UID=1000
ARG USER_GID=1000

RUN apt-get update && apt-get install -y \
        make \
        supervisor \
        librabbitmq-dev \
        autoconf \
        acl \
        libfcgi \
        file \
        gettext \
        git \
        gnupg \
        libzip-dev \
        libpq-dev \
        default-mysql-client \
        nodejs \
        npm \
        libfreetype-dev \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libonig-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        pdo_pgsql \
        zip \
        opcache \
        simplexml \
        mbstring \
    && pecl install amqp \
    && docker-php-ext-enable amqp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup --gid ${USER_UID} app && adduser --uid ${USER_UID} --gid ${USER_UID} --shell /bin/sh app

WORKDIR /var/www/app

RUN chown -R app:app /var/www/app && chmod -R 777 /var/log && chmod -R 777 /var/run

COPY ./docker/supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
EXPOSE 9000

USER app

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
