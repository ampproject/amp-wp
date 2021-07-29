/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * Internal dependencies
 */
import SourceLabel from '..';
import {
	SOURCE_TYPE_BLOCK,
	SOURCE_TYPE_CORE,
	SOURCE_TYPE_EMBED,
	SOURCE_TYPE_HOOK,
	SOURCE_TYPE_HOOK_THE_CONTENT,
	SOURCE_TYPE_HOOK_THE_EXCERPT,
	SOURCE_TYPE_MU_PLUGIN,
	SOURCE_TYPE_PLUGIN,
	SOURCE_TYPE_THEME,
} from '../../../utils/sources';
import usePluginsData from '../../plugins-context-provider/use-plugins-data';

let container;

jest.mock( '../../plugins-context-provider/use-plugins-data', () => jest.fn() );

usePluginsData.mockImplementation( () => ( {
	getPluginNameBySlug: ( slug ) => slug,
} ) );

describe( 'SourceLabel', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders nothing if no source type is provided', () => {
		act( () => {
			render(
				<SourceLabel sources={ [ 'a' ] } />,
				container,
			);
		} );

		expect( container.textContent ).toBe( '' );
	} );

	it( 'renders a single source', () => {
		act( () => {
			render(
				<SourceLabel type={ SOURCE_TYPE_PLUGIN } sources={ [ 'foo' ] } />,
				container,
			);
		} );

		expect( container.querySelector( 'details' ) ).toBeNull();
		expect( container.querySelectorAll( '.source' ) ).toHaveLength( 1 );
		expect( container.textContent ).toBe( 'foo' );
	} );

	it( 'renders multiple sources', () => {
		act( () => {
			render(
				<SourceLabel type={ SOURCE_TYPE_PLUGIN } sources={ [ 'foo', 'bar', 'baz' ] } />,
				container,
			);
		} );

		expect( container.querySelectorAll( 'details' ) ).toHaveLength( 1 );
		expect( container.querySelector( 'details .source' ).textContent ).toContain( '(3)' );

		const sourceContainers = [ ...container.querySelectorAll( 'details > div' ) ];
		expect( sourceContainers ).toHaveLength( 3 );
		expect( sourceContainers[ 0 ].textContent ).toBe( 'foo' );
		expect( sourceContainers[ 1 ].textContent ).toBe( 'bar' );
		expect( sourceContainers[ 2 ].textContent ).toBe( 'baz' );
	} );

	it( 'renders a source name inside a <code> element', () => {
		act( () => {
			render(
				<SourceLabel
					type={ SOURCE_TYPE_BLOCK }
					isCodeOutput={ true }
					sources={ [ 'foo/bar' ] }
				/>,
				container,
			);
		} );

		expect( container.querySelector( 'details' ) ).toBeNull();
		expect( container.querySelector( '.source > code' ) ).not.toBeNull();
		expect( container.querySelector( '.source > code' ).textContent ).toBe( 'foo/bar' );
	} );

	it.each( [
		[
			'Some Plugin',
			{
				type: SOURCE_TYPE_PLUGIN,
				sources: [ 'Some Plugin' ],
			},
			'.dashicons-admin-plugins',
		],
		[
			'Plugins',
			{
				type: SOURCE_TYPE_PLUGIN,
				sources: [ 'a', 'b' ],
			},
			'.dashicons-admin-plugins',
		],
		[
			'Must-Use Plugins',
			{
				type: SOURCE_TYPE_MU_PLUGIN,
				sources: [ 'a', 'b' ],
			},
			'.dashicons-admin-plugins',
		],
		[
			'Some Theme',
			{
				type: SOURCE_TYPE_THEME,
				sources: [ 'Some Theme' ],
			},
			'.dashicons-admin-appearance',
		],
		[
			'wp-includes',
			{
				type: SOURCE_TYPE_CORE,
				sources: [ 'wp-includes' ],
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Core',
			{
				type: SOURCE_TYPE_CORE,
				sources: [ 'a', 'b' ],
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Embed',
			{
				type: SOURCE_TYPE_EMBED,
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Content',
			{
				type: SOURCE_TYPE_HOOK,
				sources: [ SOURCE_TYPE_HOOK_THE_CONTENT ],
			},
			'.dashicons-edit',
		],
		[
			'Excerpt',
			{
				type: SOURCE_TYPE_HOOK,
				sources: [ SOURCE_TYPE_HOOK_THE_EXCERPT ],
			},
			'.dashicons-edit',
		],
		[
			'Hook: foo_bar',
			{
				type: SOURCE_TYPE_HOOK,
				sources: [ 'foo_bar' ],
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Some Block',
			{
				type: SOURCE_TYPE_BLOCK,
				sources: [ 'Some Block' ],
			},
			'.dashicons-edit',
		],
	] )( 'renders sources for: %s', ( title, props, icon ) => {
		act( () => {
			render(
				<SourceLabel { ...props } />,
				container,
			);
		} );

		expect( container.querySelector( icon ) ).not.toBeNull();
		expect( container.textContent ).toContain( title );
	} );
} );
