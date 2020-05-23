module.exports = {
	ci: {

		// Configure the way data is collected.
		collect: {

			// List of URLs to run the audit against.
			url: [
				'http://localhost:8080/',
				'http://localhost:8080/?amp',
			],

			// How many runs to use for averaging out variability.
			numberOfRuns: 3,

			// Chrome settings that we need for running headless within a Docker container.
			settings: {
				chromeFlags: '--headless --disable-gpu --no-sandbox --no-zygote'
			},

			// How to launch a background server to run the audit against.
			startServerCommand: "wp server --allow-root & sleep 1 && echo 'web server ready'"
		},

		// Configure the assertions that are run against the collected data.
		assert: {

			// Asserts that every audit outside performance received a perfect score, that no resources were flagged for
			// performance opportunities, and warns when metric values drop below a score of 90.
			preset: 'lighthouse:recommended',
		},

		// Configure the upload destination.
		upload: {

			// This is handled via command-line flags in GitHub workflow.
		},
	},
};
