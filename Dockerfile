FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones necesarias para CodeIgniter
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    && docker-php-ext-install intl mbstring zip pdo_mysql

# Habilitar mod_rewrite de Apache para las rutas limpias de CodeIgniter
RUN a2enmod rewrite

# Cambiar el DocumentRoot de Apache a la carpeta "public" de CodeIgniter
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -s 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -s 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar los archivos del proyecto
COPY . /var/www/html

# Establecer permisos de escritura para las carpetas writable
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public

# Instalar dependencias de PHP con Composer
RUN composer install --no-dev --optimize-autoloader

EXPOSE 80