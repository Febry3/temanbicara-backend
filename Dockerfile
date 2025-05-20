FROM php:8.3-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y --no-install-recommends\
    git curl zip unzip libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 nonroot \
    && useradd -u 1000 -ms /bin/bash -g nonroot nonroot

COPY . .

RUN chown -R nonroot:nonroot /var/www \
    && chmod -R 755 /var/www/storage

USER nonroot

EXPOSE 9000