# AMP for WordPress Validator

## Overview
The AMP for WordPress Validator runs [WP Test data](http://wptest.io) through the AMP Project's validator and returns the results.  This tool contains two parts:
1) Shell script for Installing the test data and making sure we have the wptext.xml file in our plugin root directory and
2) Node script to iterate through each post URL and validate them

## Instructions
### Installing Test data
Requirements:
- [WP-CLI](https://wp-cli.org/)
When you setup your test environment, you can easily import the test data by navigating to the plugin directory via SSH on your test server and typing `bash bin/install-amp-validator-test-data.sh`.  

This will see if the wptest.xml already exists and if it doesn't, it will place it in our plugin root.  Then it will ask you if you want to import the test data into your WP install.  If you don't have the test data installed you can select yes and the script will use WP-CLI's `import` function to add that data to your install.

(Note: WP-CLI's `import` requires the `wordpress-importer` plugin to be installed and activated.  If you don't have that installed the script will install and activate it for you.  **However, if the plugin is installed but not activated, WP-CLI will error.**)

### Validating the Test data
Requirements:
- `npm`
To make sure you have all your packages installed you can run `npm install` on the plugin directory and our package.json file will add the required packages.

We can run the program:
`node bin/run-amp-validator.js`

This will get the post URLs from the `wptest.xml` file, replace them with your test url (note: this currently assumes your permalinks are set to "Day and Name"). 

Then it will run each of those URLs through the validator.

Note: there are currently two test files included as controls (one for success and one for failure).
 
