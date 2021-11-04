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
import { Loading } from '..';

let container;

describe( 'the Loading component', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches the snapshot', () => {
		const wrapper = create( <Loading /> );

		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a loading spinner', () => {
		act( () => {
			render(
				<Loading />,
				container,
			);
		} );

		expect( container.querySelector( '.amp-spinner-container' ) ).not.toBeNull();
		expect( container.querySelector( '.components-spinner' ) ).not.toBeNull();
	} );

	it( 'renders an inline loading spinner', () => {
		act( () => {
			render(
				<Loading inline={ true } />,
				container,
			);
		} );

		expect( container.querySelector( '.amp-spinner-container--inline' ) ).not.toBeNull();
	} );
} );
