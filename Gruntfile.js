/* eslint-env node */
/* jshint node:true */

module.exports = function( grunt ) {
	'use strict';

	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		// JavaScript linting with JSHint.
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'js/*.js',
				'!js/*.min.js'
			]
		},

		// Clean up the build.
		clean: {
			build: {
				src: [ 'build' ]
			}
		},

		// Shell actions.
		shell: {
			options: {
				stdout: true,
				stderr: true
			},
			readme: {
				command: './dev-lib/generate-markdown-readme' // Generate the readme.md.
			}
		},

		// Deploys a git Repo to the WordPress SVN repo.
		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: 'amp',
					build_dir: 'build',
					assets_dir: 'wp-assets'
				}
			}
		}

	} );

	// Load tasks.
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-shell' );
	grunt.loadNpmTasks( 'grunt-wp-deploy' );

	// Register tasks.
	grunt.registerTask( 'default', [
		'build'
	] );

	grunt.registerTask( 'readme', [
		'shell:readme'
	] );

	grunt.registerTask( 'build', function() {
		var done = this.async();

		grunt.util.spawn(
			{
				cmd: 'git',
				args: [ 'ls-files' ]
			},
			function( err, res ) {
				if ( err ) {
					throw new Error( err.message );
				}

				grunt.config.set( 'copy', {
					build: {
						src: res.stdout.trim().split( /\n/ ).filter( function( file ) {
							return ! /^(\.|bin|([^/]+)+\.(md|json|xml)|Gruntfile\.js|tests|wp-assets|dev-lib|readme\.md)/.test( file );
						} ),
						dest: 'build',
						expand: true
					}
				} );
				grunt.task.run( 'readme' );
				grunt.task.run( 'copy' );
				done();
			}
		);
	} );

	grunt.registerTask( 'deploy', [
		'build',
		'jshint',
		'wp_deploy',
		'clean'
	] );
};
