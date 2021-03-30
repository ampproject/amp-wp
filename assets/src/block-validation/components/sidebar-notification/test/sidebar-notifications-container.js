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
import { SidebarNotificationsContainer } from '../index';

describe( 'SidebarNotificationsContainer', () => {
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

	it( 'renders sidebar notifications container along with children', () => {
		act( () => {
			render(
				<SidebarNotificationsContainer>
					{ 'Foo' }
				</SidebarNotificationsContainer>,
				container,
			);
		} );

		expect( container.querySelector( '.sidebar-notifications-container' ) ).not.toBeNull();
		expect( container.querySelector( '.sidebar-notifications-container' ).textContent ).toBe( 'Foo' );
	} );
} );

