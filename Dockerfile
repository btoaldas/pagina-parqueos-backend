FROM php:8.1-apache

RUN apt-get update && apt-get install -y nano

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
  echo "<Directory /var/www/html>" >> /etc/apache2/apache2.conf && \
  echo "    Options Indexes FollowSymLinks" >> /etc/apache2/apache2.conf && \
  echo "    AllowOverride All" >> /etc/apache2/apache2.conf && \
  echo "    Require all granted" >> /etc/apache2/apache2.conf && \
  echo "</Directory>" >> /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 80
