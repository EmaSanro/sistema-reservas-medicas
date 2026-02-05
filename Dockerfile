FROM php:8.5-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalamos extensiones de PHP (necesarias para muchas librerías de Composer)
RUN docker-php-ext-install pdo_mysql mysqli gd xml

# 3. Activamos módulos de Apache (Headers y Rewrite)
RUN a2enmod rewrite headers

# 4. Descargamos Composer desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Preparamos el directorio de trabajo
WORKDIR /var/www/html

# 6. Copiamos los archivos de Composer para instalar dependencias primero
# (Esto acelera las futuras construcciones de Docker)

COPY composer.json composer.lock* ./

# 7. Ejecutamos la instalación de tus dependencias
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# 8. Copiamos el resto del código del proyecto
COPY . .

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]

# 9. Ajustamos permisos para que Apache pueda leer todo
RUN chown -R www-data:www-data /var/www/html