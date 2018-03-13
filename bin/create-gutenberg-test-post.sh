#!/bin/bash
set -e

BIN_PATH="$(pwd)"
PROJECT_PATH=$(dirname $PWD)
PLUGINS_PATH=$(dirname $PROJECT_PATH)
GUTENBERG_PATH=$PLUGINS_PATH/gutenberg

# Clone the Gutenberg plugin, or pull the master branch if it's already present.
if [[ ! -e $GUTENBERG_PATH ]]; then
	cd $PLUGINS_PATH
	echo "This needs to clone the Gutenberg plugin into your plugins directory, as it looks like it's not there."
	read -p "Is that alright? y/n " -r
	printf "\n"
	if [[ $REPLY =~ [Yy] ]]; then
		git clone https://github.com/WordPress/gutenberg.git
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

cd $PROJECT_PATH/bin
wp eval-file create-gutenberg-test-post.php
