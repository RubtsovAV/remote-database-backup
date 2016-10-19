FROM php:7.0-cli
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

# Install Xdebug
RUN curl -fsSL 'https://xdebug.org/files/xdebug-2.4.0.tgz' -o xdebug.tar.gz \
    && mkdir -p xdebug \
    && tar -xf xdebug.tar.gz -C xdebug --strip-components=1 \
    && rm xdebug.tar.gz \
    && ( \
    cd xdebug \
    && phpize \
    && ./configure --enable-xdebug \
    && make -j$(nproc) \
    && make install \
    ) \
    && rm -r xdebug \
    && docker-php-ext-enable xdebug

# Clean cache of repository
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install composer && global laravel/installer
ENV COMPOSER_HOME /root/.composer
ENV PATH /root/.composer/vendor/bin:$PATH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app