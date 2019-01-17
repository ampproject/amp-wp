#!/usr/bin/env bash

set -ex

if ! wp core is-installed; then
	# Install WP core.
	wp core install \
		--url=ampwp.local \
		--title="AMP for WP" \
		--admin_user=admin \
		--admin_password=password \
		--admin_email=admin@example.com
else
	echo "WordPress already installed."
fi

# Activate the development version of the plugin.
wp plugin activate amp-src
