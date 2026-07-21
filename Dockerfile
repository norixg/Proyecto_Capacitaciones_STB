FROM node:22-bookworm AS node_builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build


FROM php:8.4-apache-bookworm

ENV ACCEPT_EULA=Y

WORKDIR /var/www/html

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        curl \
        apt-transport-https \
        ca-certificates \
        git \
        unzip \
        zip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        libssl-dev \
        unixodbc-dev \
        libgssapi-krb5-2; \
    curl -sSL -O https://packages.microsoft.com/config/debian/12/packages-microsoft-prod.deb; \
    dpkg -i packages-microsoft-prod.deb; \
    rm packages-microsoft-prod.deb; \
    apt-get update; \
    ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18; \
    pecl channel-update pecl.php.net; \
    pecl install sqlsrv pdo_sqlsrv; \
    docker-php-ext-enable sqlsrv pdo_sqlsrv; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pcntl \
        zip; \
    a2enmod rewrite headers; \
    sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf; \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias antes de copiar el código permite reutilizar la capa
# mientras composer.lock no cambie y acelera considerablemente los rebuilds.
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

COPY . .

COPY --from=node_builder /app/public/build ./public/build

COPY .docker/php.ini /usr/local/etc/php/conf.d/capacitaciones.ini
COPY .docker/apache-security.conf /etc/apache2/conf-enabled/zz-security-hardening.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint; \
    chmod +x /usr/local/bin/docker-entrypoint; \
    composer dump-autoload --no-dev --optimize --no-interaction; \
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R ug+rwX storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]
