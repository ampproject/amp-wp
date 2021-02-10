/* eslint-env node */

module.exports = function( grunt ) {
	'use strict';

	// Root paths to include in the plugin build ZIP.
	const productionIncludedRootFiles = [
		'LICENSE',
		'amp.php',
		'assets',
		'back-compat',
		'includes',
		'src',
		'templates',
	];

	// These patterns paths will be excluded from among the above directory.
	const productionExcludedPathPatterns = [
		/.*\/src\/.*/,
	];

	// These will be removed from the vendor directory after installing but prior to creating a ZIP.
	// ⚠️ Warning: These paths are passed straight to rm command in the shell, without any escaping.
	const productionVendorExcludedFilePatterns = [
		'composer.*',
		'vendor/*/*/.editorconfig',
		'vendor/*/*/.git',
		'vendor/*/*/.github',
		'vendor/*/*/.gitignore',
		'vendor/*/*/composer.*',
		'vendor/*/*/Doxyfile',
		'vendor/*/*/LICENSE',
		'vendor/*/*/phpunit.*',
		'vendor/*/*/*.md',
		'vendor/*/*/*.txt',
		'vendor/*/*/*.yml',
		'vendor/*/*/.*.yml',
		'vendor/*/*/tests',
		'vendor/ampproject/amp-toolbox/conceptual-diagram.svg',
		'vendor/bin',
		'third-party/composer.json',
		'scoper.inc.php',
	];

	// These will be removed from the Composer build of the plugin prior to creating a ZIP.
	// ⚠️ Warning: These paths are passed straight to rm command in the shell, without any escaping.
	const productionComposerExcludedFilePatterns = [
		'vendor',
		'composer.lock',
		'third-party/composer.json',
		'scoper.inc.php',
	];

	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		// Clean up the build.
		clean: {
			compiled: {
				src: [
					'assets/js/**/*.js',
					'assets/js/**/*.js.map',
					'!assets/js/amp-service-worker-runtime-precaching.js',
					'assets/js/**/*.asset.php',
					'assets/css/*.css',
					'assets/css/*.css.map',
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
			transform_readme: {
				command: 'php bin/transform-readme.php',
			},
			verify_matching_versions: {
				command: 'php bin/verify-version-consistency.php',
			},
			composer_install: {
				command: [
					'if [ ! -e build ]; then echo "Run grunt build first."; exit 1; fi',
					'mkdir -p build/vendor/bin',
					'cp vendor/bin/php-scoper build/vendor/bin/',
					'cd build',
					'composer install --no-dev -o',
					'COMPOSER_DISCARD_CHANGES=true composer remove --no-interaction --no-scripts --update-no-dev -o cweagans/composer-patches sabberworm/php-css-parser',
					'rm -rf ' + ( 'composer' === process.env.BUILD_TYPE ? productionComposerExcludedFilePatterns : productionVendorExcludedFilePatterns ).join( ' ' ),
				].join( ' && ' ),
			},
			create_build_zip: {
				command: 'if [ ! -e build ]; then echo "Run grunt build first."; exit 1; fi; if [ -e amp.zip ]; then rm amp.zip; fi; cd build; zip -r ../amp.zip .; cd ..; echo; echo "ZIP of build: $(pwd)/amp.zip"',
			},
		},

		// Deploys a git Repo to the WordPress SVN repo.
		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: 'amp',
					build_dir: 'build',
					assets_dir: '.wordpress-org',
				},
			},
		},
	} );

	// Load tasks.
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-shell' );
	grunt.loadNpmTasks( 'grunt-wp-deploy' );

	// Register tasks.
	grunt.registerTask( 'default', [
		'build',
	] );

	grunt.registerTask( 'build', function() {
		const done = this.async();
		const spawnQueue = [];
		const stdout = [];

		spawnQueue.push(
			{
				cmd: 'git',
				args: [ '--no-pager', 'log', '-1', '--format=%h', '--date=short' ],
			},
			{
				cmd: 'git',
				args: [ 'ls-files' ],
			},
		);

		function finalize() {
			const commitHash = stdout.shift();
			const lsOutput = stdout.shift();
			const versionAppend = new Date().toISOString().replace( /\.\d+/, '' ).replace( /-|:/g, '' ) + '-' + commitHash;

			const paths = lsOutput.trim().split( /\n/ ).filter( function( file ) {
				const topSegment = file.replace( /\/.*/, '' );
				if ( ! productionIncludedRootFiles.includes( topSegment ) ) {
					return false;
				}

				for ( const productionExcludedPathPattern of productionExcludedPathPatterns ) {
					if ( productionExcludedPathPattern.test( file ) ) {
						return false;
					}
				}

				return true;
			} );

			grunt.task.run( 'shell:transform_readme' );
			paths.push( 'readme.txt' );

			paths.push( 'composer.*' ); // Copy in order to be able to do run composer_install.
			paths.push( 'scoper.inc.php' ); // Copy in order generate scoped Composer dependencies.

			//  Also copy recently built assets.
			paths.push( 'assets/js/**/*.js' );
			paths.push( 'assets/js/**/*.asset.php' );
			paths.push( 'assets/css/*.css' );

			if ( 'development' === process.env.NODE_ENV ) {
				paths.push( 'assets/js/**/*.js.map' );
				paths.push( 'assets/css/*.css.map' );
			}

			// Get build version from amp.php.
			const versionRegex = /(\*\s+Version:\s+)(?<version>\d+(\.\d+)+)(?<identifier>-\w+)?/;
			const { groups: matches } = grunt.file.read( 'amp.php' ).match( versionRegex );

			if ( ! matches || ! matches.version ) {
				throw new Error( 'Plugin version could not be retrieved from amp.php' );
			}

			const version = matches.version;

			grunt.config.set( 'copy', {
				build: {
					src: paths,
					dest: 'build',
					expand: true,
					options: {
						noProcess: [ '**/*', '!amp.php', '!composer.json' ],
						process( content, srcpath ) {
							if ( /^amp\.php$/.test( srcpath ) ) {
								// If not a stable build (e.g. 0.7.0-beta), amend the version with the git commit and current timestamp.
								if ( matches.identifier ) {
									const pluginVersion = version + matches.identifier + '-' + versionAppend;
									console.log( 'Updating version in amp.php to ' + pluginVersion ); // eslint-disable-line no-console
									content = content.replace( versionRegex, '$1' + pluginVersion );
									content = content.replace( /(define\(\s*'AMP__VERSION',\s*')(.+?)(?=')/, '$1' + pluginVersion );
								}

								// Remove dev mode code blocks.
								content = content.replace( /\n\/\/\s*DEV_CODE.+?\n}\n/s, '' );

								if ( 'composer' === process.env.BUILD_TYPE ) {
									content = content.replace( "require_once AMP__DIR__ . '/vendor/autoload.php';", '' );
								}
							} else if ( /^composer\.json$/.test( srcpath ) && 'composer' === process.env.BUILD_TYPE ) {
								console.log( 'Setting version in composer.json to ' + version ); // eslint-disable-line no-console
								content = content.replace( /"name": "ampproject\/amp-wp",/, '$&\n  "version": "' + version + '",' );
							}

							return content;
						},
					},
				},
			} );
			grunt.task.run( 'copy' );
			grunt.task.run( 'shell:composer_install' );

			done();
		}

		function doNext() {
			const nextSpawnArgs = spawnQueue.shift();
			if ( ! nextSpawnArgs ) {
				finalize();
			} else {
				grunt.util.spawn(
					nextSpawnArgs,
					function( err, res ) {
						if ( err ) {
							throw new Error( err.message );
						}
						stdout.push( res.stdout );
						doNext();
					},
				);
			}
		}

		doNext();
	} );

	grunt.registerTask( 'create-build-zip', [
		'shell:create_build_zip',
	] );

	grunt.registerTask( 'deploy', [
		'shell:verify_matching_versions',
		'wp_deploy',
	] );
};
