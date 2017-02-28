FROM php:7.0-apache

COPY vendor/ /var/www/vendor/
COPY web/ /var/www/html/
COPY src/ /var/www/src/
COPY tests/ /var/www/tests/

# Install dependencies
RUN apt-get update && \
    apt-get install curl

RUN a2enmod rewrite

EXPOSE 47563