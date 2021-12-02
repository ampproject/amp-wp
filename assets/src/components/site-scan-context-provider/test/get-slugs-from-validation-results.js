/**
 * Internal dependencies
 */
import { getSourcesFromScannableUrls } from '../get-sources-from-scannable-urls';

describe( 'getSourcesFromScannableUrls', () => {
	it( 'returns empty arrays if no scannable URLs are passed', () => {
		expect( getSourcesFromScannableUrls() ).toMatchObject( { plugins: [], themes: [] } );
		expect( getSourcesFromScannableUrls( [ { url: 'https://example.com/' } ] ) ).toMatchObject( { plugins: [], themes: [] } );
	} );

	it( 'returns plugin and theme slugs', () => {
		const scannableUrls = [
			{
				url: 'https://example.com/',
				validation_errors: [
					{
						sources: [
							{ type: 'plugin', name: 'gutenberg' },
						],
					},
					{
						sources: [
							{ type: 'core', name: 'wp-includes' },
							{ type: 'plugin', name: 'amp' },
						],
					},
				],
			},
			{
				url: 'https://example.com/foo/',
				validation_errors: [
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
				],
			},
			{
				url: 'https://example.com/bar/',
				validation_errors: [
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
				],
			},
			{
				url: 'https://example.com/baz/',
				validation_errors: [
					{
						sources: [
							{ type: 'theme', name: 'twentytwenty' },
							{ type: 'core', name: 'wp-includes' },
						],
					},
					{
						sources: [
							{ type: 'plugin', name: 'jetpack' },
						],
					},
				],
			},
		];

		const slugs = getSourcesFromScannableUrls( scannableUrls );

		expect( slugs.plugins ).toStrictEqual( [
			{
				slug: 'gutenberg',
				urls: [
					'https://example.com/',
				],
			},
			{
				slug: 'jetpack',
				urls: [
					'https://example.com/foo/',
					'https://example.com/baz/',
				],
			},
			{
				slug: 'foo-bar',
				urls: [
					'https://example.com/foo/',
				],
			},
		] );
		expect( slugs.themes ).toStrictEqual( [
			{
				slug: 'twentytwenty',
				urls: [
					'https://example.com/bar/',
					'https://example.com/baz/',
				],
			},
		] );
	} );

	it( 'returns Gutenberg if it is the only plugin for a single validation error', () => {
		const scannableUrls = [
			{
				url: 'https://example.com/',
				validation_errors: [
					{
						sources: [
							{ type: 'plugin', name: 'gutenberg' },
						],
					},
				],
			},
		];

		const slugs = getSourcesFromScannableUrls( scannableUrls );

		expect( slugs.plugins ).toStrictEqual( [
			{
				slug: 'gutenberg',
				urls: [ 'https://example.com/' ],
			},
		] );
	} );

	it( 'does not return Gutenberg if there are other plugins for the same validation error', () => {
		const scannableUrls = [
			{
				url: 'https://example.com/',
				validation_errors: [
					{
						sources: [
							{ type: 'plugin', name: 'gutenberg' },
							{ type: 'plugin', name: 'jetpack' },
						],
					},
				],
			},
		];

		const slugs = getSourcesFromScannableUrls( scannableUrls );

		expect( slugs.plugins ).toStrictEqual( [
			{
				slug: 'jetpack',
				urls: [ 'https://example.com/' ],
			},
		] );
	} );

	it( 'returns a correct type of URL', () => {
		const scannableUrls = [
			{
				url: 'https://example.com/',
				amp_url: 'https://example.com/?amp=1',
				validation_errors: [
					{
						sources: [
							{ type: 'plugin', name: 'foo' },
						],
					},
				],
			},
		];

		expect( getSourcesFromScannableUrls( scannableUrls, { useAmpUrls: false } ).plugins ).toStrictEqual( [
			{
				slug: 'foo',
				urls: [ 'https://example.com/' ],
			},
		] );

		expect( getSourcesFromScannableUrls( scannableUrls, { useAmpUrls: true } ).plugins ).toStrictEqual( [
			{
				slug: 'foo',
				urls: [ 'https://example.com/?amp=1' ],
			},
		] );
	} );
} );
