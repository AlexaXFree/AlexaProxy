FROM php:8.2-apache

#Enable Apache Rewrite
Module
RUN a2enmod rewrite

WORKDIR/var/www/html

#Copy project files
COPY..

#Expose port
Expose 80

CMD ["apache2-forehround"]
