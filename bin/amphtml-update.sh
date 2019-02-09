#!/bin/bash
set -e

# Go to the right location.
cd "$(dirname "$0")"

BIN_PATH="$(pwd)"
PROJECT_PATH=$(dirname $PWD)
VENDOR_PATH="$PROJECT_PATH/vendor"

if ! command -v apt-get >/dev/null 2>&1; then
	echo "The AMP HTML uses apt-get, make sure to run this script in a Linux environment"
	exit 1
fi

# Install dependencies.
if ! dpkg -s python >/dev/null 2>&1 || ! dpkg -s protobuf-compiler >/dev/null 2>&1 || ! dpkg -s python-protobuf >/dev/null 2>&1; then
	sudo apt-get install python protobuf-compiler python-protobuf
fi

# Create and go to vendor.
if [[ ! -e $VENDOR_PATH/ampproject/amphtml ]]; then
	echo "Error: You must do composer install --dev."
	exit 1
fi

# Run script.
python amphtml-update.py > "$PROJECT_PATH/includes/sanitizers/class-amp-allowed-tags-generated.php"
