FROM php:8.2-apache

# Instalar extensiones y dependencias necesarias
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip intl

# Habilitar mod_rewrite de Apache para CodeIgniter
RUN a2enmod rewrite

# Configurar el DocumentRoot de Apache a la carpeta public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -s 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -s 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el proyecto
COPY . /var/www/html

# Permisos
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public

# Instalar dependencias automáticamente dentro del contenedor
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

EXPOSE 80