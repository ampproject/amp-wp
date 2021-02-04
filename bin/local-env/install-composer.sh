#!/bin/bash
COMPOSER_VERSION=`curl -Ls -w %{url_effective} -o /dev/null https://github.com/composer/composer/releases/latest | rev | cut -d '/' -f 1 | rev`

# Exit if any command fails
set -e

# Include useful functions
. "$(dirname "$0")/includes.sh"

# Change to the expected directory
cd "$(dirname "$0")/../.."

# Check if composer is installed
if [ "$CI" != "true" ] && ! command_exists "composer"; then
	if ask "$(error_message "Composer isn't installed, would you like to download and install it automatically?")" Y; then
		echo -en $(status_message "Installing Composer..." )
		download "https://getcomposer.org/installer" | bash >/dev/null 2>&1
		mkdir -p /usr/local/bin
		mv composer.phar /usr/local/bin/composer
		echo ' done!'
	else
		echo -e $(error_message "")
		echo -e $(error_message "Please install Composer manually, then re-run the setup script to continue.")
		echo -e $(error_message "Composer installation instructions can be found here: $(action_format "https://getcomposer.org/doc/00-intro.md")")
	fi

	exit 1
fi

# Check if the current Composer version is up to date.
if [ "$CI" != "true" ] && ! [[ "$(composer --version)" == "Composer version $COMPOSER_VERSION "* ]]; then
	echo -en $(status_message "Updating Composer..." )
	composer self-update
	echo ' done!'
fi


# Install/update packages
if [ "$CI" != "true" ]; then
	echo -e $(status_message "Installing and updating Composer packages..." )
	composer install
fi
