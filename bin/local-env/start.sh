#!/bin/bash

# Exit if any command fails
set -e

# Include useful functions
. "$(dirname "$0")/includes.sh"

# Change to the expected directory
cd "$(dirname "$0")/../.."

# Check whether Node and NVM are installed
. "$(dirname "$0")/install-node-nvm.sh"

# Check whether Composer installed
. "$(dirname "$0")/install-composer.sh"

# Check whether Docker is installed and running
. "$(dirname "$0")/launch-containers.sh"

# Set up WordPress Development site.
# Note: we don't bother installing the test site right now, because that's
# done on every time `npm run test-e2e` is run.
. "$(dirname "$0")/install-wordpress.sh"

! read -d '' AMP <<"EOT"
MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
MMMMMMMMMMMMMMMMWNXK0OOkkkkOO0KXNWMMMMMMMMMMMMMMMM
MMMMMMMMMMMMWX0kdlc:::::::::::ccodk0NWMMMMMMMMMMMM
MMMMMMMMMWXOdl::::::::::::::::::::::lx0NMMMMMMMMMM
MMMMMMMWKxl::::::::::::::::::::::::::::oOXWMMMMMMM
MMMMMMXkl:::::::::::::::::col::::::::::::oONMMMMMM
MMMMW0o:::::::::::::::::ox0Xk:::::::::::::cxXWMMMM
MMMW0l:::::::::::::::::dKWWXd:::::::::::::::dXMMMM
MMW0l::::::::::::::::cxXWMM0l::::::::::::::::dXMMM
MMXd::::::::::::::::ckNMMMWkc::::::::::::::::ckWMM
MWOc:::::::::::::::lONMMMMNkooool:::::::::::::oXMM
MWk:::::::::::::::l0WMMMMMMWNWNNOc::::::::::::l0MM
MNx::::::::::::::oKWMMMMMMMMMMW0l:::::::::::::cOWM
MNx:::::::::::::oKWWWMMMMMMMMNOl::::::::::::::c0MM
MWOc::::::::::::cddddxKWMMMMNkc:::::::::::::::oKMM
MMXd:::::::::::::::::l0MMMMXdc:::::::::::::::ckWMM
MMW0l::::::::::::::::dXMWWKd:::::::::::::::::oXMMM
MMMWOl:::::::::::::::kWW0xo:::::::::::::::::oKWMMM
MMMMW0l:::::::::::::l0NOl::::::::::::::::::dKWMMMM
MMMMMWKdc:::::::::::cooc:::::::::::::::::lkNMMMMMM
MMMMMMMN0dc::::::::::::::::::::::::::::lxKWMMMMMMM
MMMMMMMMMWKxoc::::::::::::::::::::::coOXWMMMMMMMMM
MMMMMMMMMMMWNKkdoc:::::::::::::cloxOKWMMMMMMMMMMMM
MMMMMMMMMMMMMMMWNX0OkkxxxxxxkO0KXWWMMMMMMMMMMMMMMM
MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM

EOT

CURRENT_URL=$(wp option get siteurl | tr -d '\r')

echo -e "\nWelcome to...\n"
echo -e "\033[95m$AMP\033[0m"

# Give the user more context to what they should do next: Build the plugin and start testing!
echo -e "\nRun $(action_format "npm run dev") to build the latest version of the AMP plugin,"
echo -e "then open $(action_format "$CURRENT_URL") to get started!"

echo -e "\n\nAccess the above install using the following credentials:"
echo -e "Default username: $(action_format "admin"), password: $(action_format "password")"
