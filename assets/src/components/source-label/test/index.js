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

let container;

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
				<SourceLabel source={ [ 'a' ] } />,
				container,
			);
		} );

		expect( container.textContent ).toBe( '' );
	} );

	it( 'renders a single source', () => {
		act( () => {
			render(
				<SourceLabel isPlugin={ true } source="foo" />,
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
				<SourceLabel isPlugin={ true } source={ [ 'foo', 'bar', 'baz' ] } />,
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
					isBlock={ true }
					isCodeOutput={ true }
					source="foo/bar"
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
				isPlugin: true,
				source: [ 'Some Plugin' ],
			},
			'.dashicons-admin-plugins',
		],
		[
			'Plugins',
			{
				isPlugin: true,
				source: [ 'a', 'b' ],
			},
			'.dashicons-admin-plugins',
		],
		[
			'Must-Use Plugins',
			{
				isMuPlugin: true,
				source: [ 'a', 'b' ],
			},
			'.dashicons-admin-plugins',
		],
		[
			'Some Theme',
			{
				isTheme: true,
				source: 'Some Theme',
			},
			'.dashicons-admin-appearance',
		],
		[
			'wp-includes',
			{
				isCore: true,
				source: 'wp-includes',
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Other',
			{
				isCore: true,
				source: [ 'a', 'b' ],
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Embed',
			{
				isEmbed: true,
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Content',
			{
				isHook: true,
				source: 'the_content',
			},
			'.dashicons-edit',
		],
		[
			'Excerpt',
			{
				isHook: true,
				source: 'the_excerpt',
			},
			'.dashicons-edit',
		],
		[
			'Hook: foo_bar',
			{
				isHook: true,
				source: 'foo_bar',
			},
			'.dashicons-wordpress-alt',
		],
		[
			'Some Block',
			{
				isBlock: true,
				source: 'Some Block',
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
