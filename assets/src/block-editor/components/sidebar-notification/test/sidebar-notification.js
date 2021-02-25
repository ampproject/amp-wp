/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render, unmountComponentAtNode } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { SidebarNotification } from '../index';

describe( 'SidebarNotification', () => {
	let container;

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		unmountComponentAtNode( container );
		container.remove();
		container = null;
	} );

	it( 'renders notification without icon and call to action', () => {
		act( () => {
			render( <SidebarNotification message="Foobar" />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.children ).toHaveLength( 1 );
		expect( container.querySelector( '.sidebar-notification' ) ).not.toBeNull();
		expect( container.querySelector( '.sidebar-notification__icon' ) ).toBeNull();
		expect( container.querySelector( '.sidebar-notification__content' ).textContent ).toBe( 'Foobar' );
	} );

	it( 'renders status message with icon and call to action', () => {
		act( () => {
			render(
				<SidebarNotification
					message="Foobar"
					icon={ <svg /> }
					action={ <button /> }
				/>,
				container,
			);
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.querySelector( 'svg' ) ).not.toBeNull();
		expect( container.querySelector( 'button' ) ).not.toBeNull();
	} );

	it( 'renders error notification', () => {
		act( () => {
			render(
				<SidebarNotification message="Foobar" isError={ true } />,
				container,
			);
		} );

		expect( container.querySelector( '.sidebar-notification' ).classList ).toContain( 'is-error' );
	} );
} );

