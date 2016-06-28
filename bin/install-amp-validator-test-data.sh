#!/usr/bin/env bash
set -e
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

	# Get WP Test data if a newer file exists.
	cd tests/assets && { curl -OL -z wptest.xml https://raw.githubusercontent.com/manovotny/wptest/master/wptest.xml; cd - ; }

fi

if [ "${TRAVIS}" = "true" ]; then

    cd ..
    #/tmp/wordpress/

    mkdir wp
    cd wp/

    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar

    chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp

    wp core download --version=${WP_VERSION}

    wp core config --dbname=wordpress_test --dbuser=root

    wp core install --url=http://auto-amp.dev --title=Test --admin_user=admin --admin_password=password --admin_email=test@test.com --skip-email

    mv ../amp-wp wp-content/plugins

    cd ..

    mv wp amp-wp

    cd amp-wp

    wp plugin activate amp-wp

    wp plugin install wordpress-importer --activate

    wp import wp-content/plugins/amp-wp/tests/assets/wptest.xml --authors=create --quiet

#    wp import wp-content/plugins/amp-wp/tests/assets/amptest.xml --authors=create --quiet

    wp rewrite structure '/%year%/%monthnum%/%day%/%postname%/' --hard

else

    printf "Do want to install the WPTest.io data? 'Y' or 'N': "
    read INSTALL

    if [ 'Y' = "$INSTALL" ] ; then

        wp plugin is-installed wordpress-importer
        INSTALLED=$?
        echo $[INSTALLED]

        if [ $[INSTALLED] ] ; then

            printf "Installing and Activating the WordPress importer plugin to handle our data import"
            wp plugin install wordpress-importer --activate
        fi

        wp import tests/assets/amptest.xml --authors=create --quiet

    fi

    printf "Do want to install the custom AMP Test data? 'Y' or 'N': "
    read INSTALL

    if [ 'Y' = "$INSTALL" ] ; then

        wp plugin is-installed wordpress-importer
        INSTALLED=$?
        echo $[INSTALLED]

        if [ $[INSTALLED] ] ; then

            printf "Installing and Activating the WordPress importer plugin to handle our data import"
            wp plugin install wordpress-importer --activate
        fi

        wp import tests/assets/amptest.xml --authors=create --quiet

    fi

fi