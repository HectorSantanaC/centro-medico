# Dockerfile para Render - PHP + PostgreSQL
FROM php:8.2-apache

# Instalar PostgreSQL driver
RUN docker-php-ext-install pdo_pgsql

# Copiar código app
COPY . /var/www/html/

# Configurar Apache
RUN a2enmod rewrite
EXPOSE 8080

# Render detecta puerto automáticamente
CMD ["apache2-foreground"]
