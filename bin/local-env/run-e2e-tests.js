/**
 * Wrapper for the command `wp-scripts test:e2e`.
 *
 * This allows for a greater flexibility on what specs and tests can be run based on a given environment.
 */

const path = require( 'path' );
const { spawn } = require( 'child_process' );
const semver = require( 'semver' );
const { spawnScript } = require( '@wordpress/scripts/utils' );

const composeFile = path.resolve( process.cwd(), 'bin', 'local-env', 'docker-compose.yml' );

// Retrieve the current WordPress version using the Docker CLI container.
const prc = spawn( 'docker-compose', [ '-f', composeFile, 'exec', '-T', '-u', 'xfs', 'cli', 'wp', 'core', 'version' ] );

prc.stdout.setEncoding( 'utf8' );

prc.stdout.on( 'data', ( data ) => {
	const wpVersion = data.toString().trim();

	// The first 2 args are not needed - 'node', and the script name.
	const suppliedArgs = process.argv.slice( 2 );

	const testsToIgnore = [];

	if ( semver.gte( semver.clean( wpVersion ), '5.3.0' ) ) {
		// Ignore tests that are not to be run in WP >= 5.3.0.
		testsToIgnore.push( 'AMP Settings Screen should not allow AMP Stories to be enabled when Gutenberg is not active' );
	}

	const testNamePatterns = testsToIgnore.map( ( testName ) => {
		return `--testNamePattern='^(?!${ testName }).*$'`;
	} );

	const cmdArgs = [ ...suppliedArgs, ...testNamePatterns ];

	// Run E2E tests.
	spawnScript( 'test-e2e', cmdArgs );
} );
