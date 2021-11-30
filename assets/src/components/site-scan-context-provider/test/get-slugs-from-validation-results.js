/**
 * Internal dependencies
 */
import { getSlugsFromValidationResults } from '../get-slugs-from-validation-results';

describe( 'getSlugsFromValidationResults', () => {
	it( 'returns empty arrays if no validation results are passed', () => {
		expect( getSlugsFromValidationResults() ).toMatchObject( { plugins: [], themes: [] } );
		expect( getSlugsFromValidationResults( [ { foo: 'bar' } ] ) ).toMatchObject( { plugins: [], themes: [] } );
	} );

	it( 'returns plugin and theme slugs', () => {
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
					{ type: 'plugin', name: 'foo-bar/foo-bar.php' },
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

		const slugs = getSlugsFromValidationResults( validationResult );

		expect( slugs.plugins ).toStrictEqual( [ 'gutenberg', 'jetpack', 'foo-bar' ] );
		expect( slugs.themes ).toStrictEqual( [ 'twentytwenty' ] );
	} );

	it( 'returns Gutenberg if it is the only plugin for a single validation error', () => {
		const validationResult = [
			{
				sources: [
					{ type: 'plugin', name: 'gutenberg' },
				],
			},
			{
				sources: [
					{ type: 'theme', name: 'twentytwenty' },
					{ type: 'core', name: 'wp-includes' },
				],
			},
		];

		const slugs = getSlugsFromValidationResults( validationResult );

		expect( slugs.plugins ).toStrictEqual( [ 'gutenberg' ] );
		expect( slugs.themes ).toStrictEqual( [ 'twentytwenty' ] );
	} );

	it( 'does not return Gutenberg if there are other plugins for the same validation error', () => {
		const validationResult = [
			{
				sources: [
					{ type: 'plugin', name: 'gutenberg' },
					{ type: 'plugin', name: 'jetpack' },
				],
			},
		];

		const slugs = getSlugsFromValidationResults( validationResult );

		expect( slugs.plugins ).toStrictEqual( [ 'jetpack' ] );
	} );
} );
