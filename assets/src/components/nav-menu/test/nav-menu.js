
/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { create } from 'react-test-renderer';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { NavMenu } from '../index';

let container;

const links = [
	{
		url: 'https://example.com/foo',
		label: 'Foo',
		isActive: false,
	},
	{
		url: 'https://example.com/bar',
		label: 'Bar',
		isActive: true,
	},
];

describe( 'NavMenu', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches the snapshot', () => {
		const wrapper = create( <NavMenu links={ links } /> );

		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a nav menu with a list of links', () => {
		act( () => {
			render(
				<NavMenu links={ links } />,
				container,
			);
		} );

		expect( container.querySelector( 'nav' ) ).not.toBeNull();
		expect( container.querySelector( 'ul' ) ).not.toBeNull();
		expect( container.querySelectorAll( 'li' ) ).toHaveLength( 2 );
	} );

	it( 'contains correct links', () => {
		act( () => {
			render(
				<NavMenu links={ links } />,
				container,
			);
		} );

		expect( container.querySelector( 'nav' ).textContent ).toBe( 'FooBar' );
		expect( container.querySelectorAll( 'a' ) ).toHaveLength( 2 );
		expect( container.querySelectorAll( 'a[href="https://example.com/foo"]' ) ).toHaveLength( 1 );
		expect( container.querySelectorAll( 'a[href="https://example.com/bar"]' ) ).toHaveLength( 1 );
		expect( container.querySelectorAll( 'a[class*="--active"]' ) ).toHaveLength( 1 );
		expect( container.querySelector( 'a[class*="--active"]' ).getAttribute( 'href' ) ).toBe( 'https://example.com/bar' );
	} );

	it( 'calls the handler function on click', () => {
		const handler = jest.fn();

		act( () => {
			render(
				<NavMenu links={ links } onClick={ handler } />,
				container,
			);
		} );

		act(
			() => {
				container.querySelector( 'a' ).click();
			},
		);

		expect( handler ).toHaveBeenCalledTimes( 1 );

		const [ event, link ] = handler.mock.calls[ 0 ];
		expect( event.type ).toBe( 'click' );
		expect( link ).toBe( links[ 0 ] );
	} );
} );
