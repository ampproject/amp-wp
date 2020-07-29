
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
import { Selectable } from '..';

let container;

describe( 'Selectable', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches snapshot', () => {
		let wrapper = create(
			<Selectable selected={ true }>
				<div>
					{ 'Component children' }
				</div>
			</Selectable>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();

		wrapper = create(
			<Selectable selected={ true } ElementName="section" className="my-cool-class" direction="top">
				<div>
					{ 'Component children' }
				</div>
			</Selectable>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'has correct classes', () => {
		act( () => {
			render(
				<Selectable selected={ true } ElementName="section" className="my-cool-class" direction="top">
					<div>
						{ 'children' }
					</div>
				</Selectable>,
				container,
			);
		} );

		expect( container.querySelector( 'section' ).getAttribute( 'class' ) ).toBe( 'my-cool-class selectable selectable--selected selectable--top' );

		act( () => {
			render(
				<Selectable selected={ false } ElementName="section">
					<div>
						{ 'children' }
					</div>
				</Selectable>,
				container,
			);
		} );

		expect( container.querySelector( 'section' ).getAttribute( 'class' ) ).toBe( 'selectable selectable--left' );
	} );
} );
