/**
 * Script to construct PR body for updating gutenberg packages from list of packages.
 *
 * Usage:
 * node bin/gutenberg-packages-update-pr-body.js [packages-list]
 *
 * Example:
 * node bin/gutenberg-packages-update-pr-body.js "@wordpress/api-fetch@6.8.0 @wordpress/autop@3.11.0"
 *
 * Would Output: **Following packages were updated:**<br/><br/>1. [@wordpress/api-fetch@6.8.0](https://www.npmjs.com/package/@wordpress/api-fetch/v/6.8.0)<br>2. [@wordpress/autop@3.11.0](https://www.npmjs.com/package/@wordpress/autop/v/3.11.0)<br>
 *
 */

const args = process.argv.slice( 2 );
const npmURL = 'https://www.npmjs.com/package/';
let bodyMessage = '**Following packages were updated:**\n\n';

if ( args[ 0 ] ) {
	args[ 0 ].split( ' ' ).forEach( ( packageName, index ) => {
		let occur = 0;
		const packageURL = `${ npmURL }${ packageName.replace( /@/g, ( match ) => ++occur > 1 ? '/v/' : match ) }`;
		bodyMessage += `${ index + 1 }. [${ packageName }](${ packageURL })<br>`;
	} );

	/* eslint-disable no-console */
	console.log( bodyMessage );
} else {
	console.error( 'Please provide packages list as argument.' );
	/* eslint-enable no-console */
	process.exit( 1 );
}
