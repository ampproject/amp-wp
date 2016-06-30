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

    #we are going to setup a wp install, so start from root not amp-wp
    cd ..
    mkdir wp
    cd wp/

    #install WP-CLI
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp

    #Use WP-CLI to download, configure and install WP
    wp core download --version=${WP_VERSION}
    wp core config --dbname=wordpress_test --dbuser=root
    wp core install --url=http://auto-amp.dev --title=Test --admin_user=admin --admin_password=password --admin_email=test@test.com --skip-email

    # move our WP-CLI config file out of bin into root WP
    mv ../amp-wp/bin/wp-cli.local.yml ../

    # move our amp-wp plugin from the travis root into our new plugins folder
    mv ../amp-wp wp-content/plugins

    # move to our Travis root
    cd ..

    # change the wp folder name to amp-wp and then move there so our Travis globals point to the right place
    mv wp amp-wp
    cd amp-wp

    #get the necessary plugins ready
    wp plugin activate amp-wp
#    wp plugin install wordpress-importer --activate

    #import our test data
#    wp import wp-content/plugins/amp-wp/tests/assets/wptest.xml --authors=create --quiet
#    wp import wp-content/plugins/amp-wp/tests/assets/amptest-wordpress.xml --authors=create --quiet

    #set our URL structure
#    wp rewrite structure '/%year%/%monthnum%/%day%/%postname%/' --hard

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

        wp import tests/assets/amptest-wordpress.xml --authors=create --quiet

    fi

fi