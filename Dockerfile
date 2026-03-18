# Dockerfile PHP PostgreSQL Render - PERMISOS CORREGIDOS
FROM php:8.2-apache

# Instalar PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_pgsql

# ✅ FIX PERMISOS - IMPORTANTE
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copiar app DESPUÉS de permisos
COPY . /var/www/html/

# Configurar Apache
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 8080
CMD ["apache2-foreground"]
