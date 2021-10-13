/**
 * Internal dependencies
 */
import { getSiteIssues } from '../get-site-issues';

describe( 'getSiteIssues', () => {
	it( 'returns empty arrays if no validation results are passed', () => {
		expect( getSiteIssues() ).toMatchObject( { pluginIssues: [], themeIssues: [] } );
	} );

	it( 'returns plugin and theme issues', () => {
		const validationResult = [
			{
				sources: [
					{ type: 'core', name: 'wp-includes' },
					{ type: 'plugin', name: 'amp' },
				],
			},
			{
				sources: [
					{ type: 'plugin', name: 'gutenberg' },
				],
			},
			{
				sources: [
					{ type: 'core', name: 'wp-includes' },
					{ type: 'plugin', name: 'jetpack' },
				],
			},
			{
				sources: [
					{ type: 'plugin', name: 'jetpack' },
				],
			},
			{
				sources: [
					{ type: 'plugin', name: 'foo-bar.php' },
				],
			},
			{
				sources: [
					{ type: 'theme', name: 'twentytwenty' },
					{ type: 'core', name: 'wp-includes' },
				],
			},
			{
				sources: [
					{ type: 'core', name: 'wp-includes' },
				],
			},
			{
				sources: [
					{ type: 'theme', name: 'twentytwenty' },
					{ type: 'core', name: 'wp-includes' },
				],
			},
		];

		const issues = getSiteIssues( validationResult );

		expect( issues.pluginIssues ).toStrictEqual( expect.arrayContaining( [ 'gutenberg', 'jetpack', 'foo-bar' ] ) );
		expect( issues.themeIssues ).toStrictEqual( expect.arrayContaining( [ 'twentytwenty' ] ) );
	} );
} );
