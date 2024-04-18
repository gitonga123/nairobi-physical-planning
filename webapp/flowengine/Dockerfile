FROM php:7-apache-buster

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd xdebug mbstring curl zip dom xml opcache sockets mysqli pdo_mysql

RUN echo 'date.timezone = "UTC"' >> /usr/local/etc/php/php.ini
RUN echo 'opcache.max_accelerated_files = 20000' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

COPY . /var/www/html

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

WORKDIR /var/www/html

# Run composer
# Install the latest version
RUN install-php-extensions @composer

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 777 cache/ log/


RUN composer install

CMD ["apache2-foreground"]

HEALTHCHECK --interval=5m --timeout=3s \
  CMD curl -f http://localhost/ || exit 1