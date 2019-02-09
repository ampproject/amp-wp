#!/bin/bash
set -e

# Go to the right location.
cd "$(dirname "$0")"

BIN_PATH="$(pwd)"
PROJECT_PATH=$(dirname $PWD)
VENDOR_PATH=$PROJECT_PATH/vendor

if ! command -v apt-get >/dev/null 2>&1; then
	echo "The AMP HTML uses apt-get, make sure to run this script in a Linux environment"
	exit 1
fi

# Install dependencies.
sudo apt-get install git python protobuf-compiler python-protobuf

# Create and go to vendor.
if [[ ! -e $VENDOR_PATH/ampproject/amphtml ]]; then
	echo "Error: You must do composer install --dev."
	exit 1
fi

# Copy script to location and go there.
cp $BIN_PATH/amphtml-update.py $VENDOR_PATH/ampproject/amphtml/validator
cd $VENDOR_PATH/ampproject/amphtml/validator

# Run script.
python amphtml-update.py
mv amp_wp/class-amp-allowed-tags-generated.php ../../../includes/sanitizers/
rm -r amp_wp

echo "Generated from tag $LATEST_TAG"
