/**
 * External dependencies
 */
const { executablePath } = require('puppeteer');

/**
 * WordPress dependencies
 */
const defaultPuppeteerConfig = require('@wordpress/scripts/config/puppeteer.config.js');

// eslint-disable-next-line no-console, jest/require-hook
console.log(
	`${
		process.env.CI ? '::notice::' : ''
	}Using Chromium from ${executablePath()}`
);

module.exports = {
	launch: {
		...defaultPuppeteerConfig.launch,
		executablePath: executablePath(),
	},
};
