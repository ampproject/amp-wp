#!/bin/bash
# Update includes/sanitizers/class-amp-allowed-tags-generated.php based on the AMPHTML validator spec.
#
# To update to the latest release of AMPHTML:
#
# $ ./amphtml-update.sh
#
# Update to a specific commit of AMPHTML
#
# $ git clone git@github.com:ampproject/amphtml.git amphtml
# $ cd amphtml; git checkout ec5fd60; cd -
# $ ./amphtml-update.sh amphtml/

set -e

BIN_PATH="$(dirname "$0")"
PROJECT_PATH=$(dirname $BIN_PATH)
AMPHTML_LOCATION="$1"

if ! command -v python >/dev/null 2>&1 || ! python -c "import google.protobuf" 2>/dev/null; then
	echo "Error: The google.protobuf Python module is not installed."
	echo
	echo "On Linux, you can install the required dependencies via:"
	echo "# apt-get install python protobuf-compiler python-protobuf"
	echo
	echo "On MacOS, Python is already installed but you may install via:"
	echo "$ pip install --upgrade protobuf"
	exit 1
fi

if [[ -z "$AMPHTML_LOCATION" ]]; then
	AMPHTML_VERSION=$( curl -s https://cdn.ampproject.org/rtv/metadata | sed 's/.*"ampRuntimeVersion":"01\([0-9]*\)".*/\1/' )
	CLEANUP=1

	if [[ "$AMPHTML_VERSION" =~ ^[0-9][0-9]*$ ]]; then
		echo "Current AMP version: $AMPHTML_VERSION"
	else
		echo "Unable to obtain runtime version from https://cdn.ampproject.org/rtv/metadata"
		exit 2
	fi

	AMPHTML_LOCATION=$( mktemp -d )

	curl -L "https://github.com/ampproject/amphtml/archive/$AMPHTML_VERSION.tar.gz" | tar -xzf - --strip-components=1 -C "$AMPHTML_LOCATION"
else
	CLEANUP=0
	echo "Using amphtml as located in: $AMPHTML_LOCATION"
	if [[ ! -d "$AMPHTML_LOCATION" ]]; then
		echo "Error: Directory does not exist."
		exit 3
	fi
fi

# Run script.
python "$BIN_PATH/amphtml-update.py" "$AMPHTML_LOCATION" > "$PROJECT_PATH/includes/sanitizers/class-amp-allowed-tags-generated.php"

if [[ $CLEANUP == 1 ]]; then
	rm -r "$AMPHTML_LOCATION"
fi

if [[ ! -z "$AMPHTML_VERSION" ]]; then
	echo ""
	echo "Please review the diff before committing:"
	echo "Update allowed tags/attributes from spec in amphtml $AMPHTML_VERSION"
fi
