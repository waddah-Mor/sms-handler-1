FROM php:7.0-apache

# Would like to build vendors on image build really
COPY vendor/ /var/www/vendor/
COPY web/ /var/www/html/

# Install dependencies
RUN apt-get update && \
    apt-get install curl

RUN a2enmod rewrite
RUN a2enmod dir

EXPOSE 80
