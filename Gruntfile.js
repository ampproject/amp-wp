/* eslint-env node */

module.exports = function (grunt) {
	'use strict';

	// Root paths to include in the plugin build ZIP when running `npm run build:prod`.
	const productionIncludedRootFiles = [
		'LICENSE',
		'amp.php',
		'assets',
		'back-compat',
		'includes',
		'src',
		'templates',
		'uninstall.php',
		'vendor',
	];

	// These patterns paths will be excluded from among the above directory.
	const productionExcludedPathPatterns = [/.*\/src\/.*/];

	// These will be removed from the build directory after installing but prior to creating a ZIP.
	// ⚠️ Warning: These paths are passed straight to rm command in the shell, without any escaping.
	const productionInstallExcludedFilePatterns = [
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
		'vendor/ampproject/amp-toolbox/.gitattributes',
		'vendor/ampproject/amp-toolbox/.phpcs.xml.dist',
		'vendor/ampproject/amp-toolbox/bin',
		'vendor/ampproject/amp-toolbox/conceptual-diagram.svg',
		'vendor/ampproject/amp-toolbox/phpstan.neon.dist',
		'vendor/bin',
	];

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

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
				src: ['build'],
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
					'cd build',
					'composer install --no-dev -o',
					'composer remove cweagans/composer-patches --update-no-dev -o',
					'rm -rf ' + productionInstallExcludedFilePatterns.join(' '),
				].join(' && '),
			},
			create_build_zip: {
				command:
					'if [ ! -e build ]; then echo "Run grunt build first."; exit 1; fi; if [ -e amp.zip ]; then rm amp.zip; fi; cd build; zip -r ../amp.zip .; cd ..; echo; echo "ZIP of build: $(pwd)/amp.zip"',
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
	});

	// Load tasks.
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-wp-deploy');

	// Register tasks.
	grunt.registerTask('default', ['build']);

	grunt.registerTask('build', function () {
		const done = this.async();
		const spawnQueue = [];
		const stdout = [];

		spawnQueue.push({
			cmd: 'git',
			args: ['ls-files'],
		});

		// If the script is executed within a GHA job in a PR, use the last PR commit hash instead of the one from the
		// currently checked out merge commit.
		if (!process.env.LAST_PR_COMMIT_HASH) {
			spawnQueue.push({
				cmd: 'git',
				args: [
					'--no-pager',
					'log',
					'-1',
					'--format=%h',
					'--date=short',
				],
			});
		}

		function finalize() {
			const lsOutput = stdout.shift();
			const commitHash = process.env.LAST_PR_COMMIT_HASH
				? process.env.LAST_PR_COMMIT_HASH.slice(0, 9)
				: stdout.shift();
			const versionAppend =
				new Date()
					.toISOString()
					.replace(/\.\d+/, '')
					.replace(/-|:/g, '') +
				'-' +
				commitHash;

			const paths = lsOutput
				.trim()
				.split(/\n/)
				.filter(function (file) {
					const topSegment = file.replace(/\/.*/, '');
					if (!productionIncludedRootFiles.includes(topSegment)) {
						return false;
					}

					for (const productionExcludedPathPattern of productionExcludedPathPatterns) {
						if (productionExcludedPathPattern.test(file)) {
							return false;
						}
					}

					return true;
				});

			grunt.task.run('shell:transform_readme');
			paths.push('readme.txt');

			paths.push('composer.*'); // Copy in order to be able to do run composer_install.
			paths.push('assets/js/**/*.js');
			paths.push('assets/js/**/*.asset.php');
			paths.push('assets/css/*.css');

			if ('development' === process.env.NODE_ENV) {
				paths.push('assets/js/**/*.js.map');
				paths.push('assets/css/*.css.map');
			}

			grunt.config.set('copy', {
				build: {
					src: paths,
					dest: 'build',
					expand: true,
					options: {
						noProcess: ['*/**', 'LICENSE'], // That is, only process amp.php and README.md.
						process(content, srcpath) {
							let matches, version, versionRegex;
							if (/amp\.php$/.test(srcpath)) {
								versionRegex =
									/(\*\s+Version:\s+)(\d+(\.\d+)+-\w+)/;

								// If not a stable build (e.g. 0.7.0-beta), amend the version with the git commit and current timestamp.
								matches = content.match(versionRegex);
								if (matches) {
									version = matches[2] + '-' + versionAppend;
									// eslint-disable-next-line no-console
									console.log(
										'Updating version in amp.php to ' +
											version
									);
									content = content.replace(
										versionRegex,
										'$1' + version
									);
									content = content.replace(
										/(define\(\s*'AMP__VERSION',\s*')(.+?)(?=')/,
										'$1' + version
									);
								}

								// Remove dev mode code blocks.
								content = content.replace(
									/\n\/\/\s*DEV_CODE.+?\n}\n/s,
									''
								);
							}
							return content;
						},
					},
				},
			});
			grunt.task.run('copy');
			grunt.task.run('shell:composer_install');

			done();
		}

		function doNext() {
			const nextSpawnArgs = spawnQueue.shift();
			if (!nextSpawnArgs) {
				finalize();
			} else {
				grunt.util.spawn(nextSpawnArgs, function (err, res) {
					if (err) {
						throw new Error(err.message);
					}
					stdout.push(res.stdout);
					doNext();
				});
			}
		}

		doNext();
	});

	grunt.registerTask('create-build-zip', ['shell:create_build_zip']);

	grunt.registerTask('deploy', [
		'shell:verify_matching_versions',
		'wp_deploy',
	]);
};
