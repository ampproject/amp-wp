#!/usr/bin/env bash
set -ev
#
# WP-AMP Validator Tests Script
#
# Note: This script assumes you have the following installed:
#  - wp-cli
#
# from within the plugin root director enter 'bash bin/install-amp-validator-test-data.sh' to run the installer
#####################################################################################

# Check if our test data exists. If it already does not, we will get it from github.
if ! [ -f "wptest.xml" ]
then

	# Get WP Test data.
	curl -OL https://raw.githubusercontent.com/manovotny/wptest/master/wptest.xml

fi

if [ "${TRAVIS}" = "true" ]; then

    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar

    echo ${PWD}

    php wp-cli.phar --info

    chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp

    echo ${PWD}


    wp --info

    wp plugin install wordpress-importer --activate

    wp import wptest.xml --authors=create


else

    printf "Do want to install the Test data or have you already installed it? 'Y' or 'N': "
    read INSTALL

    if [ 'Y' = "$INSTALL" ] ; then

        wp plugin is-installed wordpress-importer
        INSTALLED=$?
        echo $[INSTALLED]

        if [ $[INSTALLED] ] ; then

            printf "Installing and Activating the WordPress importer plugin to handle our data import"
            wp plugin install wordpress-importer --activate
        fi

        wp import wptest.xml --authors=create

    fi

fi