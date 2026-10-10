FROM php:8.5-cli-alpine

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apk --no-cache update && \
    docker-php-ext-install exif pdo pdo_mysql

WORKDIR /app

COPY ["composer.json", "composer.lock", "./"]
RUN composer install --no-scripts

COPY ["docker-entrypoint.sh", "/usr/local/bin/"]
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
USER 1000
