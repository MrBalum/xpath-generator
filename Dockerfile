# Basisimage
FROM php:7.4-cli

# Arbeitsverzeichnis in Container
WORKDIR /app

# Kopiere Projektdateien in Container
COPY . /app

# Installiere erforderliche PHP-Erweiterungen und Abhängigkeiten
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev
