/* eslint-env node */

module.exports = function ( grunt ) {
	'use strict';

	// prettier-ignore
	grunt.initConfig( {
		// Build a deploy-able plugin.
		copy: {
			build: {
				src: [
					'assets/**',
					'!assets/src/**',
					'src/**',
					'composer.json',
					'amp-qa-tester.php',
				],
				dest: 'build',
				expand: true,
				dot: true,
			},
		},

		// Clean up the build.
		clean: {
			compiled: {
				src: [
					'assets/js/',
					'assets/css/',
				],
			},
			build: {
				src: [ 'build' ],
			},
		},

		// Shell actions.
		shell: {
			options: {
				stdout: true,
				stderr: true,
			},
			install_composer_build: {
				command:
				'cd build;' +
				'composer install --no-dev -o;' +
				'rm composer.json composer.lock;' +
				'cd ..;' +
				'echo;' +
				'echo "Composer dependencies for build installed successfully!"',
			},
			create_build_zip: {
				command:
					'if [ ! -e build ]; then echo "Run grunt build first."; exit 1; fi;' +
					' if [ -e amp-qa-tester.zip ]; then rm amp-qa-tester.zip; fi;' +
					'cd build;' +
					'zip -r ../amp-qa-tester.zip .;' +
					'cd ..;' +
					'echo;' +
					'echo "ZIP of build: $(pwd)/amp-qa-tester.zip"',
			},
		},
	} );

	// Load tasks.
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-shell' );

	// Register tasks.
	grunt.registerTask( 'default', [ 'build' ] );

	grunt.registerTask( 'build', [ 'copy' ] );

	grunt.registerTask( 'install-composer-build', [
		'shell:install_composer_build',
	] );

	grunt.registerTask( 'create-build-zip', [ 'shell:create_build_zip' ] );
};
