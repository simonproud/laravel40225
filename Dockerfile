FROM php:8.4-fpm-alpine

# Get current host user and group id
ARG USER_ID
ARG GROUP_ID

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    shadow \
    php-bcmath \
    nodejs \
    npm

# Create user with same ID as host user
RUN addgroup -g ${GROUP_ID} laravel && \
    adduser -u ${USER_ID} -G laravel -s /bin/sh -D laravel

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Set ownership to laravel user
RUN chown -R laravel:laravel /var/www

# Switch to laravel user
USER laravel

# Install project dependencies
RUN composer install
RUN npm install
