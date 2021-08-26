
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
import { RadioGroup } from '../radio-group';

let container;

const options = [
	{
		value: 'a',
		title: 'Foo',
	},
	{
		value: 'b',
		title: 'Bar',
	},
];

describe( 'RadioGroup', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches the snapshot', () => {
		const wrapper = create( <RadioGroup options={ options } /> );

		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders radio buttons in a form element', () => {
		act( () => {
			render(
				<RadioGroup options={ options } />,
				container,
			);
		} );

		expect( container.querySelector( 'form' ) ).not.toBeNull();
		expect( container.querySelectorAll( 'input[type="radio"]' ) ).toHaveLength( 2 );
		expect( container.querySelector( 'input[type="radio"]:checked' ) ).toBeNull();
		expect( container.querySelector( 'form' ).textContent ).toBe( 'FooBar' );
	} );

	it( 'renders label with a matching for attribute', () => {
		act( () => {
			render(
				<RadioGroup options={ options } />,
				container,
			);
		} );

		const forAttribute = container.querySelector( 'label:first-child' ).getAttribute( 'for' );
		expect( container.querySelector( 'label:first-child input' ).getAttribute( 'id' ) ).toBe( forAttribute );
	} );

	it( 'renders an initially selected option', () => {
		act( () => {
			render(
				<RadioGroup options={ options } selected="b" />,
				container,
			);
		} );

		expect( container.querySelector( 'input[type="radio"]:checked' ) ).not.toBeNull();
		expect( container.querySelector( 'input[type="radio"]:checked' ).closest( 'label' ).textContent ).toBe( 'Bar' );
		expect( container.querySelector( 'input[type="radio"]:not(checked)' ) ).not.toBeNull();
	} );

	it( 'calls the handler function on selection change', () => {
		const handler = jest.fn();

		act( () => {
			render(
				<RadioGroup options={ options } onChange={ handler } />,
				container,
			);
		} );

		act(
			() => {
				container.querySelector( 'label' ).dispatchEvent( new global.MouseEvent( 'click' ) );
			},
		);

		expect( handler ).toHaveBeenCalledTimes( 1 );
		expect( handler ).toHaveBeenCalledWith( 'a' );
	} );
} );
