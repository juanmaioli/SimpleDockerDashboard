# Dockerfile para PHP 8.4 con Apache y Docker CLI
FROM php:8.4-apache

# Instalamos dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    ca-certificates \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalamos el CLIENTE de Docker (Docker CLI) desde el binario estático
RUN curl -fsSL https://download.docker.com/linux/static/stable/x86_64/docker-27.3.1.tgz | tar -xzC /usr/local/bin --strip-components=1 docker/docker

# --- INCORPORACIÓN DE CONFIGURACIONES EN LA IMAGEN ---

# Copiamos la configuración personalizada de Apache
COPY ./apache_data/ /etc/apache2/

# Copiamos la configuración personalizada de PHP
COPY ./php_data/php.ini /usr/local/etc/php/php.ini

# Habilitamos módulos de Apache (aseguramos que estén activos independientemente de los archivos copiados)
RUN a2enmod rewrite headers

# Creamos el grupo docker con el GID correcto (126) para que coincida con el host
# Y agregamos www-data a ese grupo
RUN groupadd -g 126 docker_host && usermod -aG docker_host www-data

# Ajustamos permisos de la web
RUN chown -R www-data:www-data /var/www/html
