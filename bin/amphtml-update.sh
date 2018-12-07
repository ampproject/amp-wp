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
if [[ ! -e $VENDOR_PATH ]]; then
	mkdir $VENDOR_PATH
fi
cd $VENDOR_PATH

# Clone amphtml repo.
if [[ ! -e $VENDOR_PATH/amphtml ]]; then
	git clone https://github.com/ampproject/amphtml amphtml
else
	cd $VENDOR_PATH/amphtml/validator
	git fetch --tags
fi

# Check out the latest tag.
cd $VENDOR_PATH/amphtml
LATEST_TAG=$( git describe --abbrev=0 --tags )
git checkout $LATEST_TAG

# Copy script to location and go there.
cp $BIN_PATH/amphtml-update.py $VENDOR_PATH/amphtml/validator
cd $VENDOR_PATH/amphtml/validator

# Run script.
python amphtml-update.py
mv amp_wp/class-amp-allowed-tags-generated.php ../../../includes/sanitizers/
rm -r amp_wp

echo "Generated from tag $LATEST_TAG"
