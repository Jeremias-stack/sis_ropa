FROM node:22-alpine AS assets
WORKDIR /build
COPY package*.json vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/
RUN npm ci --ignore-scripts && npm run build

FROM php:8.4-cli
RUN apt-get update && apt-get install -y --no-install-recommends \
  git unzip libzip-dev libicu-dev libonig-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo_mysql mbstring zip intl gd bcmath \
  && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-interaction --no-progress --optimize-autoloader
COPY . .
COPY --from=assets /build/public/build ./public/build
RUN rm -f bootstrap/cache/*.php && php artisan package:discover --ansi
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
