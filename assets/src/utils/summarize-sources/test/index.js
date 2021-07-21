/**
 * Internal dependencies
 */
import summarizeSources from '..';

describe( 'summarizeSources', () => {
	it( 'renders null if no sources array is provided', () => {
		const result = summarizeSources();
		expect( result ).toBeNull();
	} );

	it( 'returns correct sources summary', () => {
		const result = summarizeSources( [
			{
				hook: 'wp_head',
				type: 'plugin',
				name: 'foo',
			},
			{
				type: 'theme',
				name: 'bar',
			},
			{
				type: 'plugin',
				name: 'baz',
			},
			{
				hook: 'init',
				type: 'plugin',
				block_name: 'foobar',
			},
			{
				embed: 'bazbar',
			},
		] );
		expect( result ).toStrictEqual( {
			hook: 'init',
			plugin: [ 'foo', 'baz' ],
			theme: [ 'bar' ],
			blocks: [ 'foobar' ],
			embed: true,
		} );
	} );

	it( 'does not return core if there is a plugin or theme', () => {
		const result = summarizeSources( [
			{
				type: 'plugin',
				name: 'foo',
			},
			{
				type: 'core',
				name: 'bar',
			},
		] );
		expect( result ).toStrictEqual( {
			plugin: [ 'foo' ],
		} );
	} );

	it( 'returns core if there is no plugin or theme', () => {
		const result = summarizeSources( [
			{
				type: 'core',
				name: 'baz',
			},
		] );
		expect( result ).toStrictEqual( {
			core: [ 'baz' ],
		} );
	} );

	it( 'returns de-duped themes and plugins', () => {
		const result = summarizeSources( [
			{
				type: 'plugin',
				name: 'foo',
			},
			{
				type: 'plugin',
				name: 'foo',
			},
			{
				type: 'mu-plugin',
				name: 'bar',
			},
			{
				type: 'mu-plugin',
				name: 'bar',
			},
			{
				type: 'theme',
				name: 'baz',
			},
			{
				type: 'theme',
				name: 'baz',
			},
		] );
		expect( result ).toStrictEqual( {
			plugin: [ 'foo' ],
			'mu-plugin': [ 'bar' ],
			theme: [ 'baz' ],
		} );
	} );
} );
