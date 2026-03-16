<<<<<<< HEAD
FROM php:8.3-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier le code Laravel
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Installer et builder les assets JS
RUN npm install && npm run build

# Permissions
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Config Apache
RUN a2enmod rewrite
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf
RUN sed -i "s|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g" /etc/apache2/sites-available/000-default.conf

# Cache Laravel (env from Render)
RUN chmod +x start.sh

# Exposer le port 80
EXPOSE 80

# Lancer via start.sh (runtime)
CMD ["./start.sh"]
=======
FROM php:8.2-fpm

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y git unzip libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Installer composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier le projet
WORKDIR /var/www/html
COPY . .

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Lancer Laravel au démarrage
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
>>>>>>> a2601d8 (app ready again for deployement fly.io)
