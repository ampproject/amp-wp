/**
 * Internal dependencies
 */
import summarizeSources from '..';

describe( 'summarizeSources', () => {
	it( 'renders null if no sources array is provided', () => {
		const result = summarizeSources();
		expect( result ).toBeNull();
	} );

	it( 'returns plugin and theme, and skips everything else', () => {
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
				type: 'mu-plugin',
				name: 'baz',
			},
			{
				hook: 'init',
				type: 'plugin',
				block_name: 'foobar',
			},
		] );
		expect( result ).toStrictEqual( {
			plugin: [ 'foo' ],
			muPlugin: [ 'baz' ],
			theme: [ 'bar' ],
		} );
	} );

	it( 'does not return theme if embed is present', () => {
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
			plugin: [ 'foo', 'baz' ],
		} );
	} );

	it( 'returns core if there is no plugin or theme', () => {
		const result = summarizeSources( [
			{
				type: 'core',
				name: 'baz',
			},
			{
				embed: 'bazbar',
			},
			{
				type: 'core',
				name: 'bar',
			},
		] );
		expect( result ).toStrictEqual( {
			core: [ 'baz', 'bar' ],
		} );
	} );

	it( 'returns embed if there is no plugin, theme or core', () => {
		const result = summarizeSources( [
			{
				embed: true,
			},
			{
				block_name: 'foobar',
			},
		] );
		expect( result ).toStrictEqual( {
			embed: true,
		} );
	} );

	it( 'returns blocks if there is no plugin, theme, core or embed', () => {
		const result = summarizeSources( [
			{
				block_name: 'foobar',
			},
			{
				block_name: 'bazbar',
			},
		] );
		expect( result ).toStrictEqual( {
			blocks: [ 'foobar', 'bazbar' ],
		} );
	} );

	it( 'returns hook if there is no plugin, theme, core, embed or blocks', () => {
		const result = summarizeSources( [
			{
				hook: 'baz',
			},
		] );
		expect( result ).toStrictEqual( {
			hook: 'baz',
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
			muPlugin: [ 'bar' ],
			theme: [ 'baz' ],
		} );
	} );
} );
