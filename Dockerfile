# Dockerfile PHP + PostgreSQL para Render - CORREGIDO
FROM php:8.2-apache

# 1. Instalar librerías PostgreSQL + dependencias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar PHP PostgreSQL drivers
RUN docker-php-ext-install pdo_pgsql

# 3. Copiar código de la app
COPY . /var/www/html/

# 4. Configurar Apache
RUN a2enmod rewrite \
    && sed -i 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/000-default.conf

# 5. Puerto para Render
EXPOSE 8080

CMD ["apache2-foreground"]
