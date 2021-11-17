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
import {
	AdminNotice,
	ADMIN_NOTICE_TYPE_INFO,
	ADMIN_NOTICE_TYPE_SUCCESS,
	ADMIN_NOTICE_TYPE_WARNING,
	ADMIN_NOTICE_TYPE_ERROR,
} from '..';

let container;

describe( 'AdminNotice', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches the snapshot', () => {
		const wrapper = create( <AdminNotice /> );

		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a plain admin notice', () => {
		act( () => {
			render(
				<AdminNotice>
					{ 'Content' }
				</AdminNotice>,
				container,
			);
		} );

		expect( container.querySelector( '.admin-notice' ) ).not.toBeNull();
		expect( container.querySelector( '.admin-notice' ).textContent ).toBe( 'Content' );
	} );

	it( 'renders a dismissable admin notice', () => {
		const onDismiss = jest.fn();

		act( () => {
			render(
				<AdminNotice isDismissible={ true } onDismiss={ onDismiss } />,
				container,
			);
		} );

		expect( container.querySelector( '.admin-notice--dismissible' ) ).not.toBeNull();
		expect( container.querySelector( '.admin-notice__dismiss' ) ).not.toBeNull();

		act( () => {
			container.querySelector( '.admin-notice__dismiss' ).click();
		} );

		expect( onDismiss ).toHaveBeenCalledTimes( 1 );
	} );

	it.each( [
		[ ADMIN_NOTICE_TYPE_INFO ],
		[ ADMIN_NOTICE_TYPE_SUCCESS ],
		[ ADMIN_NOTICE_TYPE_WARNING ],
		[ ADMIN_NOTICE_TYPE_ERROR ],
	] )( 'renders a "%s" admin notice type', ( type ) => {
		act( () => {
			render(
				<AdminNotice type={ type } />,
				container,
			);
		} );

		expect( container.querySelector( `.admin-notice--${ type }` ) ).not.toBeNull();
	} );
} );
