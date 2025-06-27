FROM php:8.4-apache

RUN apt-get update
RUN apt-get install python3-certbot-apache curl -y

# Installation les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite

# Ajoutez à votre Dockerfile existant :
RUN a2enmod ssl
# Assurez-vous que le site par défaut est désactivé
RUN touch /etc/apache2/sites-available/ticketsapp.conf && a2ensite ticketsapp.conf

# S'assurer que les ports sont bien configurés
EXPOSE 80 443
