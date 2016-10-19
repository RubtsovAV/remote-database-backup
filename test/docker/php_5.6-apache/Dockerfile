FROM php:5.6-apache
MAINTAINER RubtsovAV@gmail.com

# Installing dependencies
RUN apt-get update && apt-get upgrade -y
RUN apt-get -y install \
    bzip2 \
    freetds-dev \
    git \
    libicu-dev \
    libmysqlclient-dev \
    libpng12-dev \
    libpq-dev \
    libxml2-dev \
    zlib1g-dev

# Configuring extensions to compile
RUN docker-php-ext-configure pdo_dblib --with-libdir=lib/x86_64-linux-gnu/

# Compiling and installing extensions
RUN docker-php-ext-install \
    intl \
    mbstring \
    mysqli \
    pdo_dblib \
    pdo_mysql \
    pdo_pgsql \
    xmlrpc \
    zip \
    opcache

# Clean cache of repository
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*