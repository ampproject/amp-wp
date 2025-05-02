#!/bin/bash

set -e

WP_VERSION=$1
INSTALL_PWA_PLUGIN=${2-false}

if [[ -z $WP_VERSION ]]; then
	echo "usage: $0 <wp-version> [install-pwa-plugin]"
	exit 1
fi

gb_version="trunk"

if [[ "$gb_version" != "" ]]; then
	echo -n "Installing Gutenberg ${gb_version}..."

	url_path=$([ $gb_version == "trunk" ] && echo "trunk" || echo "tags/${gb_version}")
	gutenberg_plugin_svn_url="https://plugins.svn.wordpress.org/gutenberg/${url_path}/"
	svn export -q "$gutenberg_plugin_svn_url" "$WP_CORE_DIR/src/wp-content/plugins/gutenberg"
	echo "done"
fi

if [[ -n $INSTALL_PWA_PLUGIN ]]; then
	echo -n "Installing PWA plugin..."
	wget -O "$WP_CORE_DIR/src/wp-content/plugins/pwa.zip" https://downloads.wordpress.org/plugin/pwa.zip
	unzip -d "$WP_CORE_DIR/src/wp-content/plugins/" "$WP_CORE_DIR/src/wp-content/plugins/pwa.zip"
	echo "done"
fi
