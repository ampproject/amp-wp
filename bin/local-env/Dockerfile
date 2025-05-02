# @todo: switch to wordpress:latest once it's available
FROM wordpress:beta-6.7-php8.3-apache

# WP CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
	&& chmod +x wp-cli.phar \
	&& mv wp-cli.phar /usr/local/bin/wp

CMD ["apache2-foreground"]
