#!/bin/bash

# Exit if any command fails.
set -e

# Include useful functions
. "$(dirname "$0")/includes.sh"

# Make sure Docker containers are running
dc up -d >/dev/null 2>&1

# Get the host port for the WordPress container.
HOST_PORT=$(dc port $CONTAINER 80 | awk -F : '{printf $2}')

# Wait until the Docker containers are running and the WordPress site is
# responding to requests.
echo -en $(status_message "Attempting to connect to WordPress...")
until $(curl -L http://localhost:$HOST_PORT -so - 2>&1 | grep -q "WordPress"); do
    echo -n '.'
    sleep 5
done
echo ''

# WordPress Importer plugin is needed for the "wp import" command.
echo -e $(status_message "Installing WordPress Importer plugin...")
wp plugin install wordpress-importer --activate --quiet

# Download the latest version of the theme unit test data into the container and import it.
# TODO: This is a moving target. Use fixed commit hash or integrate into this repository?
echo -e $(status_message "Importing Theme Unit data...")
container curl -s -o /var/www/html/wp-content/uploads/themeunittestdata.wordpress.xml \
  https://raw.githubusercontent.com/WPTRT/theme-unit-test/master/themeunittestdata.wordpress.xml
wp import /var/www/html/wp-content/uploads/themeunittestdata.wordpress.xml --authors=create

# Monster post creation taken from https://github.com/westonruter/amp-wp-theme-compat-analysis/
# TODO: This should be integrated into this repository.
echo -e $(status_message "Creating monster post...")
container curl -s -o /var/www/html/wp-content/mu-plugins/create-monster-post.php \
  https://raw.githubusercontent.com/westonruter/amp-wp-theme-compat-analysis/master/public/wp-content/mu-plugins/create-monster-post.php
wp create-monster-post

# Widget population taken from https://github.com/westonruter/amp-wp-theme-compat-analysis/
# TODO: This should be integrated into this repository.
echo -e $(status_message "Populating widget areas...")
container curl -s -o /var/www/html/wp-content/mu-plugins/widget-population.php \
  https://raw.githubusercontent.com/westonruter/amp-wp-theme-compat-analysis/master/public/wp-content/mu-plugins/widget-population.php
wp populate-initial-widgets
