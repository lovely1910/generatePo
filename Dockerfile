FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libreoffice-writer \
    libreoffice-calc \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

EXPOSE 10000

CMD php -S 0.0.0.0:${PORT:-10000} -t public
