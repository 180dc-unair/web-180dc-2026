FROM php:8.4-fpm

WORKDIR /var/www/html

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    default-mysql-client \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    ca-certificates \
    gnupg

RUN groupmod --gid "${APP_GID}" www-data \
 && usermod --uid "${APP_UID}" --gid "${APP_GID}" www-data

# Install Node 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
 && apt-get install -y nodejs

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

CMD ["php-fpm"]
