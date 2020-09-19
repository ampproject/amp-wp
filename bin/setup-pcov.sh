#!/bin/bash

# Note: This script is solely intended for setting up the PCOV driver for PHPUnit in Travis CI.

phpenv config-rm xdebug.ini || echo "xdebug.ini does not exist."
pecl install pcov
echo "pcov.directory=." >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# PHPUnit has to be installed from source to allow pcov/cobbler to patch it.
composer global require --dev --ignore-platform-reqs phpunit/phpunit:^7 pcov/clobber
$(composer config home)/vendor/bin/pcov $(composer config home) clobber
