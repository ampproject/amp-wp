#!/bin/bash
set -e
cd "$(dirname "$0")"

BIN_PATH="$(pwd)"
PROJECT_PATH=$(dirname $PWD)
PLUGINS_PATH=$(dirname $PROJECT_PATH)
GUTENBERG_PATH=$PLUGINS_PATH/gutenberg

# Clone the Gutenberg plugin, or pull the master branch if it's already present.
if [[ ! -e $GUTENBERG_PATH ]]; then
	cd $PLUGINS_PATH
	echo "This needs to clone the Gutenberg plugin into your plugins directory, as it looks like it's not there."
	read -p "Is that alright? y/n " -r
	if [[ $REPLY =~ [Yy] ]]; then
		git clone https://github.com/WordPress/gutenberg.git
		wp plugin activate gutenberg
		echo "The Gutenberg plugin is cloned. Please follow the build steps:"
		echo "https://github.com/WordPress/gutenberg/blob/master/CONTRIBUTING.md"
	else
		echo "Exiting script."
		exit 1
	fi
else
	cd $GUTENBERG_PATH
	if [ 'master' == $( git rev-parse --abbrev-ref HEAD ) ]; then
		git pull origin master
	fi
fi

cd $PROJECT_PATH
wp eval-file bin/create-gutenberg-test-post.php
