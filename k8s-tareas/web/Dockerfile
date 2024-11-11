# Usamos una imagen base con Apache y PHP 8.3
FROM php:8.3-apache

# Eliminar todo el contenido existente en /var/www/html
RUN rm -rf /var/www/html/*

# Instalar las extensiones necesarias para PDO y MySQL
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Copiamos nuestros archivos PHP/HTML a la carpeta de Apache
COPY www/tareas/ /var/www/html/

# Configuramos permisos para Apache
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Exponemos el puerto 80
EXPOSE 80
