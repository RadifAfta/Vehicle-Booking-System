# STAGE 1: Base PHP & Extensions
FROM php:8.3-fpm-alpine AS base

WORKDIR /var/www

# 1. Install System Dependencies & PHP Extensions (Lengkap untuk Filament)
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    libxslt-dev \
    linux-headers \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath intl mbstring zip gd xml dom xsl

# 2. Copy Composer dari image resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Copy file konfigurasi composer duluan (untuk caching)
COPY composer.json composer.lock ./

# 4. FIX UTAMA: Tambahkan --no-scripts
# Ini mencegah Composer mencari file 'artisan' yang belum ada
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --no-scripts

# 5. Sekarang baru copy SELURUH file project (termasuk file artisan)
COPY . .

# 6. Jalankan ulang dump-autoload untuk memetakan class (Opsional tapi aman)
RUN composer dump-autoload --optimize --no-dev

# STAGE 2: Frontend Build
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./vite.config.js
# Jika build butuh env, buat file kosong saja
RUN touch .env && npm run build

# STAGE 3: Final Runtime
FROM base AS runtime
WORKDIR /var/www

# Copy hasil build frontend ke folder public
COPY --from=frontend /app/public/build /var/www/public/build

# Setup Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Atur permission agar Laravel bisa menulis log/cache
RUN chown -R www-data:www-data /var/www

USER www-data

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]