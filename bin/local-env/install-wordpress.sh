#!/bin/bash

# Exit if any command fails.
set -e

# Common variables.
WP_DEBUG=${WP_DEBUG-true}
SCRIPT_DEBUG=${SCRIPT_DEBUG-true}
WP_VERSION=${WP_VERSION-"latest"}

# Include useful functions
. "$(dirname "$0")/includes.sh"

# Make sure Docker containers are running
dc up -d >/dev/null 2>&1

# Get the host port for the WordPress container.
HOST_PORT=$(dc port $CONTAINER 80 | awk -F : '{printf $2}')

# Wait until the WordPress site is responding to requests.
echo -en $(status_message "Attempting to connect to WordPress...")
until $(curl -L http://localhost:$HOST_PORT -so - 2>&1 | grep -q "WordPress"); do
    echo -n '.'
    sleep 5
done
echo ''

# Wait until the database container is ready.
echo -en $(status_message "Waiting for database connection...")
until $(container bash -c "echo -n > /dev/tcp/mysql/3306" >/dev/null 2>&1); do
    echo -n '.'
    sleep 5
done
echo ''

# Create the database if it doesn't exist.
echo -e $(status_message "Creating the database (if it does not exist)...")
mysql -e 'CREATE DATABASE IF NOT EXISTS wordpress;'

# If this is the test site, we reset the database so no posts/comments/etc.
# dirty up the tests.
if [ "$1" == '--reset-site' ]; then
	echo -e $(status_message "Resetting test database...")
	wp db reset --yes --quiet
fi

if [ ! -z "$WP_VERSION" ] && [ "$WP_VERSION" != "latest" ]; then
	# Potentially downgrade WordPress
	echo -e $(status_message "Downloading WordPress version $WP_VERSION...")
	wp core download --version=${WP_VERSION} --force --quiet
fi

# Install WordPress.
echo -e $(status_message "Installing WordPress...")
wp config list
wp core install --title="$SITE_TITLE" --admin_user=admin --admin_password=password --admin_email=test@test.com --skip-email --url=http://localhost:$HOST_PORT  --quiet

# Create additional users.
echo -e $(status_message "Creating additional users...")
wp user create editor editor@example.com --role=editor --user_pass=password --quiet
echo -e $(status_message "Editor created! Username: editor Password: password")
wp user create author author@example.com --role=author --user_pass=password --quiet
echo -e $(status_message "Author created! Username: author Password: password")
wp user create contributor contributor@example.com --role=contributor --user_pass=password --quiet
echo -e $(status_message "Contributor created! Username: contributor Password: password")
wp user create subscriber subscriber@example.com --role=subscriber --user_pass=password --quiet
echo -e $(status_message "Subscriber created! Username: subscriber Password: password")

# Make sure the uploads and upgrade folders exist and we have permissions to add files.
echo -e $(status_message "Ensuring that files can be uploaded...")
container mkdir -p \
	/var/www/html/wp-content/uploads \
	/var/www/html/wp-content/upgrade
container chmod 767 \
	/var/www/html/wp-content \
	/var/www/html/wp-content/plugins \
	/var/www/html/wp-config.php \
	/var/www/html/wp-settings.php \
	/var/www/html/wp-content/uploads \
	/var/www/html/wp-content/upgrade

CURRENT_WP_VERSION=$(wp core version | tr -d '\r')
echo -e $(status_message "Current WordPress version: $CURRENT_WP_VERSION...")

if [ "$WP_VERSION" == "latest" ]; then
	# Check for WordPress updates, to make sure we're running the very latest version.
	echo -e $(status_message "Updating WordPress to the latest version...")
	wp core update --quiet
	echo -e $(status_message "Updating The WordPress Database...")
	wp core update-db --quiet
fi

# If the 'wordpress' volume wasn't during the down/up earlier, but the post port has changed, we need to update it.
echo -e $(status_message "Checking the site's url...")
CURRENT_URL=$(wp option get siteurl)
if [ "$CURRENT_URL" != "http://localhost:$HOST_PORT" ]; then
	wp option update home "http://localhost:$HOST_PORT" --quiet
	wp option update siteurl "http://localhost:$HOST_PORT" --quiet
fi

# Install a dummy favicon to avoid 404 errors.
echo -e $(status_message "Installing a dummy favicon...")
container touch /var/www/html/favicon.ico
container chmod 767 /var/www/html/favicon.ico

# Activate AMP plugin.
echo -e $(status_message "Activating AMP plugin...")
wp plugin activate amp --quiet

# Install & activate Gutenberg plugin.
echo -e $(status_message "Installing and activating Gutenberg plugin...")
wp plugin install gutenberg --activate --force --quiet

# Set pretty permalinks.
echo -e $(status_message "Setting permalink structure...")
wp rewrite structure '%postname%' --hard --quiet

# Configure site constants.
echo -e $(status_message "Configuring site constants...")
WP_DEBUG_CURRENT=$(wp config get --type=constant --format=json WP_DEBUG | tr -d '\r')

if [ "$WP_DEBUG" != $WP_DEBUG_CURRENT ]; then
	wp config set WP_DEBUG $WP_DEBUG --raw --type=constant --quiet
	WP_DEBUG_RESULT=$(wp config get --type=constant --format=json WP_DEBUG | tr -d '\r')
	echo -e $(status_message "WP_DEBUG: $WP_DEBUG_RESULT...")
fi

SCRIPT_DEBUG_CURRENT=$(wp config get --type=constant --format=json SCRIPT_DEBUG | tr -d '\r')
if [ "$SCRIPT_DEBUG" != $SCRIPT_DEBUG_CURRENT ]; then
	wp config set SCRIPT_DEBUG $SCRIPT_DEBUG --raw --type=constant --quiet
	SCRIPT_DEBUG_RESULT=$(wp config get --type=constant --format=json SCRIPT_DEBUG | tr -d '\r')
	echo -e $(status_message "SCRIPT_DEBUG: $SCRIPT_DEBUG_RESULT...")
fi
