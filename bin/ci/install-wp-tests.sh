#!/usr/bin/env bash

set -e

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-true}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo "$TMPDIR" | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}

download() {
    if [ $(which curl) ]; then
        curl -s "$1" > "$2";
    elif [ $(which wget) ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
	WP_BRANCH=${WP_VERSION%\-*}
	WP_TESTS_TAG="branches/$WP_BRANCH"

elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
	if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
		# version x.x.0 means the first release of the major version, so strip off the .0 and download version x.x
		WP_TESTS_TAG="tags/${WP_VERSION%??}"
	else
		WP_TESTS_TAG="tags/$WP_VERSION"
	fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep -E '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

install_wp() {

	if [ -d "$WP_CORE_DIR" ]; then
		return;
	fi

	if grep -isqE 'trunk|alpha|beta|rc' <<< "$WP_VERSION"; then
		local SVN_URL=https://develop.svn.wordpress.org/trunk/
	elif [ "$WP_VERSION" == 'latest' ]; then
		local TAG=$( svn ls https://develop.svn.wordpress.org/tags | tail -n 1 | sed 's:/$::' )
		local SVN_URL="https://develop.svn.wordpress.org/tags/$TAG/"
	elif [[ "$WP_VERSION" =~ ^[0-9]+\.[0-9]+$ ]]; then
		# Use the release branch if no patch version supplied. This is useful to keep testing the latest minor version.
		local SVN_URL="https://develop.svn.wordpress.org/branches/$WP_VERSION/"
	else
		local SVN_URL="https://develop.svn.wordpress.org/tags/$WP_VERSION/"
	fi

	echo "Installing WP from $SVN_URL to $WP_CORE_DIR"

	svn export -q "$SVN_URL" "$WP_CORE_DIR"

	# Download `wp-includes` folder from the WordPress Core SVN repo to include built internal dependencies.
	local SVN_CORE_URL=${SVN_URL/develop/core}
	svn export -q --force "${SVN_CORE_URL}wp-includes" "$WP_CORE_DIR/src/wp-includes"

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php "$WP_CORE_DIR"/src/wp-content/db.php
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i.bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d "$WP_TESTS_DIR" ]; then
		# set up testing suite
		mkdir -p "$WP_TESTS_DIR"
		svn co --quiet --ignore-externals https://develop.svn.wordpress.org/"${WP_TESTS_TAG}"/tests/phpunit/includes/ "$WP_TESTS_DIR/includes"
		svn co --quiet --ignore-externals https://develop.svn.wordpress.org/"${WP_TESTS_TAG}"/tests/phpunit/data/ "$WP_TESTS_DIR/data"
	fi

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
		# remove all forward slashes in the end
		WP_CORE_DIR="$(echo $WP_CORE_DIR | sed "s:/\+$::")"
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/src/':" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR/wp-tests-config.php"
	fi

}

install_db() {

	if [ "${SKIP_DB_CREATE}" = "true" ]; then
		return 0
	fi

	# parse DB_HOST for port or socket references
	local PARTS=("${DB_HOST//\:/ }")
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if [ -n "$DB_HOSTNAME" ] ; then
		if [ "$(echo "$DB_SOCK_OR_PORT" | grep -e '^[0-9]\{1,\}$')" ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif [ -n "$DB_SOCK_OR_PORT" ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif [ -n "$DB_HOSTNAME" ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS""$EXTRA"
}

install_wp
install_test_suite
install_db
