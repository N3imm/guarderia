# Usar una imagen oficial de PHP con Apache
FROM php:8.1-apache

# Instalar extensiones de PHP necesarias
# pdo_mysql para la conexión con MySQL
# mbstring y libxml son comunes para frameworks y manejo de strings/XML
RUN docker-php-ext-install pdo_mysql mbstring libxml

# Habilitar mod_rewrite de Apache para URLs amigables (muy común en proyectos PHP)
RUN a2enmod rewrite

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar todos los archivos del proyecto al directorio de trabajo del contenedor
COPY . /var/www/html/

# Opcional: Asegurar que el usuario de Apache tenga permisos (si hay problemas de escritura)
# RUN chown -R www-data:www-data /var/www/html/assets/images/uploads
