# Dockerfile

# Use the official PHP image that includes Apache
FROM php:8.2-apache

# Copy your PHP, HTML, and CSS files into the container's web root
COPY html/ /var/www/html/

# (Optional) If your PHP code needs common extensions, you can install them.
# Uncomment the line below to install mysqli for MySQL or pgsql for PostgreSQL.
# RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Expose port 80 for Apache
EXPOSE 80