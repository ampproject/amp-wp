/**
 * Internal dependencies
 */
const Changelog = require( './changelog' );

const getArgs = () => {
	const matches = Array.from(
		process.argv
			.slice( 2 )
			.join( ' ' )
			.matchAll( /--([a-z]+)(?:[ =]([^\s]+))?/g ),
	);

	return matches.reduce( ( acc, cur ) => {
		const [ , key, value = true ] = cur;
		acc[ key ] = value;
		return acc;
	}, {} );
};

const { milestone, token = process.env.GITHUB_TOKEN } = getArgs();

if ( ! milestone ) {
	process.stderr.write( 'Please supply a milestone name via the "--milestone" argument.\n' );
	process.exit( 1 );
}

if ( ! token ) {
	process.stderr.write( `
Please supply a GitHub token via the "--token" argument or "GITHUB_TOKEN" environment variable.\n
The token must have the following permissions at minimum for the changelog to be generated:
"public_repo", "read:org", "read:user", "repo:status" and "user:email".\n
` );

	process.exit( 1 );
}

new Changelog( 'ampproject/amp-wp', milestone, token ).generate()
	.then( ( changelog ) => process.stdout.write( changelog ) + '\n' )
	.catch( ( e ) => {
		process.stderr.write( e + '\n' );
		process.exit( 1 );
	} );
