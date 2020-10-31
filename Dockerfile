FROM php:7.4-apache
COPY . /var/www/html
RUN DEBIAN_FRONTEND=noninteractive apt-get update && apt-get install -y curl
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libmemcached-dev \
    libmemcached-tools \
    zlibc \
    zlib1g \
    lsb-release \
    libgmp-dev \
    expect-dev \
    git \
    nano \
    python \
    locales \
    libsnmp-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN set -ex \
    && rm -rf /var/lib/apt/lists/* \
    && MEMCACHED="`mktemp -d`" \
    && curl -skL https://github.com/php-memcached-dev/php-memcached/archive/master.tar.gz | tar zxf - --strip-components 1 -C $MEMCACHED \
    && docker-php-ext-configure $MEMCACHED \
    && docker-php-ext-install $MEMCACHED \
    && rm -rf $MEMCACHED
RUN a2enmod rewrite && \
    service apache2 restart && \
    chown www-data:www-data -R /var/www/html
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
EXPOSE 80 3306 443 11211