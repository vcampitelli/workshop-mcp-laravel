FROM php:8.4-cli-alpine

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apk --no-cache update && \
    docker-php-ext-install exif pdo pdo_mysql
    
WORKDIR /app

COPY ["composer.json", "composer.lock", "./"]
RUN composer install --no-scripts

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
