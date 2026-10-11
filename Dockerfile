FROM php:8.4-cli-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
