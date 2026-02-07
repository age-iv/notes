FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libssl-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl
RUN docker-php-ext-enable opcache

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www

# Копируем зависимости
COPY ./backend/composer.json ./backend/composer.lock* ./

# Устанавливаем зависимости с оптимизацией
RUN if [ -f composer.json ]; then composer install --no-scripts --no-autoloader; fi

# Копируем остальные файлы
COPY ./backend .

# Генерируем автозагрузку
RUN if [ -f composer.json ]; then composer dump-autoload --optimize; fi

# Настройка прав
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www

EXPOSE 9000
