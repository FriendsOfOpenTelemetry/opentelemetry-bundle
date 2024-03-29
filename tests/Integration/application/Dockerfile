FROM php:8.2

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libz-dev

RUN docker-php-ext-install pdo pdo_pgsql

RUN pecl install grpc \
    && docker-php-ext-enable grpc

RUN pecl install opentelemetry \
    && docker-php-ext-enable opentelemetry

WORKDIR /app

EXPOSE 8000

CMD php -S 0.0.0.0:8000 -t public
