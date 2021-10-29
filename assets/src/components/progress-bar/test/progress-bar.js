
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
import { ProgressBar } from '../index';

let container;

describe( 'ProgressBar', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches the snapshot', () => {
		const wrapper = create( <ProgressBar value={ 33 } /> );

		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a progress bar', () => {
		act( () => {
			render(
				<ProgressBar value={ 33 } />,
				container,
			);
		} );

		expect( container.querySelector( '.progress-bar[role="progressbar"]' ) ).not.toBeNull();
		expect( container.querySelector( '.progress-bar[aria-valuemin="0"]' ) ).not.toBeNull();
		expect( container.querySelector( '.progress-bar[aria-valuemax="100"]' ) ).not.toBeNull();
		expect( container.querySelector( '.progress-bar[aria-valuenow="33"]' ) ).not.toBeNull();
	} );

	it( 'the bar is shifted correctly', () => {
		act( () => {
			render(
				<ProgressBar value={ 75 } />,
				container,
			);
		} );

		expect( container.querySelector( '.progress-bar[aria-valuenow="75"]' ) ).not.toBeNull();
		expect( container.querySelector( '.progress-bar__indicator' ) ).not.toBeNull();
		expect( container.querySelector( '.progress-bar__indicator' ).style.transform ).toBe( 'translateX(-25%)' );
	} );

	it( 'does not allow the bar to be completely out of view for low values', () => {
		act( () => {
			render(
				<ProgressBar value={ 1 } />,
				container,
			);
		} );

		expect( container.querySelector( '.progress-bar[aria-valuenow="1"]' ) ).not.toBeNull();
		expect( container.querySelector( '.progress-bar__indicator' ) ).not.toBeNull();
		expect( container.querySelector( '.progress-bar__indicator' ).style.transform ).toBe( 'translateX(-97%)' );
	} );
} );
