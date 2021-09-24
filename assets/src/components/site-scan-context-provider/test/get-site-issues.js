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
				error: {
					sources: [
						{ type: 'core', name: 'wp-includes' },
						{ type: 'plugin', name: 'amp' },
					],
				},
			},
			{
				error: {
					sources: [
						{ type: 'plugin', name: 'gutenberg' },
					],
				},
			},
			{
				error: {
					sources: [
						{ type: 'core', name: 'wp-includes' },
						{ type: 'plugin', name: 'jetpack' },
					],
				},
			},
			{
				error: {
					sources: [
						{ type: 'plugin', name: 'jetpack' },
					],
				},
			},
			{
				error: {
					sources: [
						{ type: 'plugin', name: 'foo-bar.php' },
					],
				},
			},
			{
				error: {
					sources: [
						{ type: 'theme', name: 'twentytwenty' },
						{ type: 'core', name: 'wp-includes' },
					],
				},
			},
			{
				error: {
					sources: [
						{ type: 'core', name: 'wp-includes' },
					],
				},
			},
			{
				error: {
					sources: [
						{ type: 'theme', name: 'twentytwenty' },
						{ type: 'core', name: 'wp-includes' },
					],
				},
			},
		];

		const issues = getSiteIssues( validationResult );

		expect( issues.pluginIssues ).toStrictEqual( expect.arrayContaining( [ 'gutenberg', 'jetpack', 'foo-bar' ] ) );
		expect( issues.themeIssues ).toStrictEqual( expect.arrayContaining( [ 'twentytwenty' ] ) );
	} );
} );
