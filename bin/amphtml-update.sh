#!/bin/bash
set -e

# Go to the right location.
if [[ '.' != $(dirname "$0") ]]; then
	cd bin
fi

BIN_PATH="$(pwd)"
PROJECT_PATH=$(dirname $PWD)
VENDOR_PATH=$PROJECT_PATH/vendor

# Create and go to vendor.
if [[ ! -e $VENDOR_PATH ]]; then
	mkdir $VENDOR_PATH
fi
cd $VENDOR_PATH

# Clone amphtml repo.
if [[ ! -e $VENDOR_PATH/amphtml ]]; then
	git clone https://github.com/ampproject/amphtml amphtml
fi

# Copy script to location and go there.
cp $BIN_PATH/amphtml-update.py $VENDOR_PATH/amphtml/validator
cd $VENDOR_PATH/amphtml/validator

# Temporary fix until https://github.com/ampproject/amphtml/issues/12371 is addressed.
if [ ! -f $VENDOR_PATH/amphtml/validator/validator_gen_md.py ]; then
	git apply $BIN_PATH/amphtml-fix.diff
fi

# Install dependencies.
sudo apt-get install python
sudo apt-get install protobuf-compiler
sudo apt-get install python-protobuf

# Run script.
python amphtml-update.py
cp amp_wp/class-amp-allowed-tags-generated.php ../../../includes/sanitizers/
