# Usar una imagen oficial de PHP con Apache
FROM php:8.1-apache

# Instalar dependencias del sistema y luego las extensiones de PHP
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache para URLs amigables (muy común en proyectos PHP)
RUN a2enmod rewrite

# Copiar la configuración de Apache para apuntar al directorio /public
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar todos los archivos del proyecto al directorio de trabajo del contenedor
COPY . /var/www/html/

# Opcional: Asegurar que el usuario de Apache tenga permisos (si hay problemas de escritura)
# RUN chown -R www-data:www-data /var/www/html/assets/images/uploads
