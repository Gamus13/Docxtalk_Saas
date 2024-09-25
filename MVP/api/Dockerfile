FROM php:8.2.11-fpm


# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/html/


# Set working directory
WORKDIR /var/www/html



# Install dependencies
RUN apt-get update \
    && apt-get install -y gnupg curl wget ca-certificates unzip lsb-release \
    && wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
    && echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" | tee  /etc/apt/sources.list.d/pgdg.list \
    && curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        nodejs \
        postgresql-client-14


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*



# Install extensions
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install intl pdo pdo_pgsql pgsql zip bcmath pcntl exif



#enabled opcache
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="10000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="192" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10"


RUN docker-php-ext-install opcache
COPY ./opcache.ini /usr/local/etc/php/conf.d/opcache.ini



# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www


# Copy existing application directory contents
COPY . /var/www/html


# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html


RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache



# Change current user to www
USER www


# Expose port 8000 for the PHP-FPM server
EXPOSE 8000

