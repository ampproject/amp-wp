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
import { AmpLayoutControl } from '../';

let container;

describe( 'AmpLayoutControl', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'should not render if ampLayout is undefined', function() {
		act( () => {
			render( <AmpLayoutControl setAttributes={ () => {} } attributes={ {} } />, container );
		} );

		const selectControl = container.querySelector( 'select' );
		expect( selectControl ).toBeNull();
	} );

	it( 'should render if ampLayout is defined', function() {
		act( () => {
			render( <AmpLayoutControl setAttributes={ () => {} } attributes={ { ampLayout: '' } } />, container );
		} );

		const selectControl = container.querySelector( 'select' );
		expect( selectControl ).not.toBeNull();
	} );
} );
