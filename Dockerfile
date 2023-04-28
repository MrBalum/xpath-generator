# Basisimage
FROM php:7.4-cli

# Arbeitsverzeichnis in Container
WORKDIR /app

# Installiere erforderliche PHP-Erweiterungen und Abhängigkeiten
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Kopiere erst nur die Dependencies ...
COPY composer.json composer.lock .

# ... um sie mit Composer zu installieren
RUN composer install --no-dev

# Kopiere Projektdateien in Container, nach den Dependencies. Verbessert Caching.
COPY . .

# Definiere Standard-Befehle
ENTRYPOINT ["php"]
CMD ["main.php"]
