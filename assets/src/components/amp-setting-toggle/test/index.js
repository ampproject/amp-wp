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
import { AMPSettingToggle } from '../';

let container;

describe( 'AMPSettingToggle', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches snapshots', () => {
		let wrapper = create(
			<AMPSettingToggle checked={ true } onChange={ () => null } text={ 'My text' } title={ 'My title' } />,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();

		wrapper = create(
			<AMPSettingToggle checked={ false } onChange={ () => null } title={ 'My title' } />,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'has correct elements and text', () => {
		act( () => {
			render(
				<AMPSettingToggle title="My title" onChange={ () => null } checked={ false }>
					{ 'children' }
				</AMPSettingToggle>,
				container,
			);
		} );

		expect( container.querySelector( 'h3' ).textContent ).toBe( 'My title' );
		expect( container.querySelector( 'p' ) ).toBeNull();
		expect( container.querySelector( 'input:checked' ) ).toBeNull();

		act( () => {
			render(
				<AMPSettingToggle title="My title" onChange={ () => null } checked={ true } text="My text">
					{ 'children' }
				</AMPSettingToggle>,
				container,
			);
		} );

		expect( container.querySelector( 'h3' ).textContent ).toBe( 'My title' );
		expect( container.querySelector( 'p' ).textContent ).toBe( 'My text' );
		expect( container.querySelector( 'input:checked' ) ).not.toBeNull();
	} );
} );
