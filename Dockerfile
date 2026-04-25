FROM node:20-alpine AS frontend
WORKDIR /app
RUN corepack enable && corepack prepare yarn@1.22.21 --activate
COPY package.json yarn.lock ./
RUN yarn install --frozen-lockfile
COPY resources ./resources
COPY public ./public
COPY vite.config.js tsconfig.json postcss.config.js tailwind.config.js components.json ./
RUN yarn build

FROM php:8.4-cli
WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring intl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
COPY --from=frontend /app/public/build ./public/build
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

CMD ["sh", "-c", "test -n \"$APP_KEY\" || (echo 'APP_KEY is missing. Set it in Render env vars.' && exit 1); php artisan migrate --force && php artisan config:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
