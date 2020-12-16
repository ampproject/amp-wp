/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { AmpNoloadingToggle } from '../';

let container;

describe( 'AmpNoloadingToggle', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'should not render if ampNoLoading is undefined', function() {
		act( () => {
			render( <AmpNoloadingToggle setAttributes={ () => {} } attributes={ {} } />, container );
		} );

		const selectControl = container.querySelector( 'input' );
		expect( selectControl ).toBeNull();
	} );

	it( 'should render if ampNoLoading is defined', function() {
		act( () => {
			render( <AmpNoloadingToggle setAttributes={ () => {} } attributes={ { ampNoLoading: true } } />, container );
		} );

		const selectControl = container.querySelector( 'input' );
		expect( selectControl ).not.toBeNull();
	} );
} );
