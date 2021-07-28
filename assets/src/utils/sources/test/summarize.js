/**
 * Internal dependencies
 */
import {
	SOURCE_TYPE_PLUGIN,
	SOURCE_TYPE_MU_PLUGIN,
	SOURCE_TYPE_THEME,
	SOURCE_TYPE_CORE,
	SOURCE_TYPE_EMBED,
	SOURCE_TYPE_BLOCK,
	SOURCE_TYPE_HOOK,
	summarizeSources,
} from '..';

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
			[ SOURCE_TYPE_PLUGIN ]: [ 'foo' ],
			[ SOURCE_TYPE_MU_PLUGIN ]: [ 'baz' ],
			[ SOURCE_TYPE_THEME ]: [ 'bar' ],
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
			[ SOURCE_TYPE_PLUGIN ]: [ 'foo', 'baz' ],
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
			[ SOURCE_TYPE_CORE ]: [ 'baz', 'bar' ],
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
			[ SOURCE_TYPE_EMBED ]: true,
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
			[ SOURCE_TYPE_BLOCK ]: [ 'foobar', 'bazbar' ],
		} );
	} );

	it( 'returns hook if there is no plugin, theme, core, embed or blocks', () => {
		const result = summarizeSources( [
			{
				hook: 'baz',
			},
		] );
		expect( result ).toStrictEqual( {
			[ SOURCE_TYPE_HOOK ]: 'baz',
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
			[ SOURCE_TYPE_PLUGIN ]: [ 'foo' ],
			[ SOURCE_TYPE_MU_PLUGIN ]: [ 'bar' ],
			[ SOURCE_TYPE_THEME ]: [ 'baz' ],
		} );
	} );
} );
