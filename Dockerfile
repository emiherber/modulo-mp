FROM php:8.2-apache

# Install Nano
RUN apt update && apt install -y nano

# Install SQL SERVER drivers
ENV ACCEPT_EULA=Y
RUN apt update && apt install -y gnupg2
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - 
RUN curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list > /etc/apt/sources.list.d/mssql-release.list 
RUN apt update 
RUN ACCEPT_EULA=Y apt-get -y --no-install-recommends install msodbcsql17 
RUN apt install -y unixodbc-dev
RUN pecl install sqlsrv
RUN pecl install pdo_sqlsrv
RUN docker-php-ext-enable sqlsrv pdo_sqlsrv

# Install GD
RUN apt update && apt install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install Intl
RUN apt update \
    && apt install -y libicu-dev \
    && docker-php-ext-install intl
    
# Install PHP-Zip
RUN apt install -y libzip-dev \
        zip \
  && docker-php-ext-install zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Define PHP_TIMEZONE env variable
ENV PHP_TIMEZONE America/Argentina/Buenos_Aires

# Configure Apache Document Root
ENV APACHE_DOC_ROOT /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite

#CMD ["/bin/sh"]
