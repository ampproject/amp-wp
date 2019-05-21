/**
 * External dependencies
 */
import { render } from 'enzyme';

/**
 * Internal dependencies
 */
import FontFamilyPicker from '../';

const { ampStoriesFonts } = window;

describe( 'FontFamilyPicker', () => {
	it( 'should render a default button if no font is selected', () => {
		const fontFamilyPicker = render( <FontFamilyPicker fonts={ ampStoriesFonts } /> );
		expect( fontFamilyPicker ).toMatchSnapshot();
	} );

	it( 'should render the selected font name', () => {
		const fontFamilyPicker = render( <FontFamilyPicker fonts={ ampStoriesFonts } value="Arial" /> );
		expect( fontFamilyPicker ).toMatchSnapshot();
	} );

	it( 'should render the selected font svg preview', () => {
		const fontFamilyPicker = render( <FontFamilyPicker fonts={ ampStoriesFonts } value="Roboto" /> );
		expect( fontFamilyPicker ).toMatchSnapshot();
	} );
} );
