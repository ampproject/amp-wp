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
	git pull
fi

# Copy script to location and go there.
cp $BIN_PATH/amphtml-update.py $VENDOR_PATH/amphtml/validator
cd $VENDOR_PATH/amphtml/validator

# Temporary fix until https://github.com/ampproject/amphtml/issues/12371 is addressed.
if [ ! -f $VENDOR_PATH/amphtml/validator/validator_gen_md.py ]; then
	git apply $BIN_PATH/amphtml-fix.diff
fi

# Run script.
python amphtml-update.py
cp amp_wp/class-amp-allowed-tags-generated.php ../../../includes/sanitizers/
