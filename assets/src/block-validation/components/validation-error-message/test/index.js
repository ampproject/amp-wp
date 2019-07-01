/**
 * External dependencies
 */
import { render } from 'enzyme';

/**
 * Internal dependencies
 */
import ValidationErrorMessage from '../';

describe( 'ValidationErrorMessage', () => {
	it( 'renders an error with a custom message', () => {
		const errorMessage = render( <ValidationErrorMessage message="Hello World" /> );
		expect( errorMessage ).toMatchSnapshot();
	} );

	it( 'renders an error for an invalid element', () => {
		const errorMessage = render( <ValidationErrorMessage code="invalid_element" node_name="foo" /> );
		expect( errorMessage ).toMatchSnapshot();
	} );

	it( 'renders an error for an invalid attribute', () => {
		const errorMessage = render( <ValidationErrorMessage code="invalid_attribute" node_name="bar" /> );
		expect( errorMessage ).toMatchSnapshot();
	} );

	it( 'renders an error for an invalid attribute with parent node', () => {
		const errorMessage = render( <ValidationErrorMessage code="invalid_attribute" node_name="bar" parent_name="baz" /> );
		expect( errorMessage ).toMatchSnapshot();
	} );

	it( 'renders an error for a custom error code', () => {
		const errorMessage = render( <ValidationErrorMessage code="some_other_error" /> );
		expect( errorMessage ).toMatchSnapshot();
	} );

	it( 'renders an error for an unknown error code', () => {
		const errorMessage = render( <ValidationErrorMessage /> );
		expect( errorMessage ).toMatchSnapshot();
	} );
} );
