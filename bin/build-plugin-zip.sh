#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..
PLUGIN_DIR=$(pwd)

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
GREEN_BOLD='\033[1;32m';
RED_BOLD='\033[1;31m';
YELLOW_BOLD='\033[1;33m';
COLOR_RESET='\033[0m';
error () {
	echo -e "\n${RED_BOLD}$1${COLOR_RESET}\n"
}
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}
success () {
	echo -e "\n${GREEN_BOLD}$1${COLOR_RESET}\n"
}
warning () {
	echo -e "\n${YELLOW_BOLD}$1${COLOR_RESET}\n"
}

status "Time to release AMP ⚡️"

status "Setting up a fresh build environment in a temporary folder. ✨"

# Create a fresh temporary folder in a way that works across platforms.
BUILD_DIR=$(mktemp -d 2>/dev/null || mktemp -d -t 'amp-production-build')

# Do a local clone to move the current files across.
git clone -l . "$BUILD_DIR"
cd "$BUILD_DIR"

# Run the build.
status "Installing dependencies... 📦"
composer install -o
PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true npm install

status "Generating build... ⚙️"
npm run build

status "Copying the ZIP file back over... ↩️"
rm -f "$PLUGIN_DIR/amp.zip"
cp "$BUILD_DIR/amp.zip" "$PLUGIN_DIR/amp.zip"

status "Removing the temporary folder again... 🗑️"
rm -rf "$BUILD_DIR"

success "You've built AMP! 🎉 \nThe ZIP file can be found in the following location:\n$PLUGIN_DIR/amp.zip"
