# Stage 1 : image PHP finale
FROM php:8.5-cli AS runner

WORKDIR /app

RUN echo "expose_php=Off" > /usr/local/etc/php/conf.d/security.ini


# Installation des dépendances système et des extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    && rm -rf /var/lib/apt/lists/*


# Récupération de Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# Copie des fichiers Composer
COPY composer.json composer.lock symfony.lock ./


# Installation des dépendances PHP
RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-dev \
    --no-scripts \
    --optimize-autoloader


# Installation de Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get update \
    && apt-get install -y symfony-cli \
    && rm -rf /var/lib/apt/lists/*


# Copie des fichiers du projet
COPY bin ./bin
COPY config ./config
COPY migrations ./migrations
COPY public ./public
COPY src ./src
COPY templates ./templates


# Création des dossiers nécessaires à Symfony
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data /app


# Définit le dossier personnel utilisé par Symfony CLI
ENV HOME=/app


# Utilisation d'un utilisateur non root
USER www-data


# Port utilisé par l'API
EXPOSE 8000


HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -f http://localhost:8000/health/ || exit 1

# Démarrage du serveur Symfony
CMD ["symfony", "server:start", "--allow-http", "--no-tls", "--listen-ip=0.0.0.0"]