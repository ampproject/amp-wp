jest.mock( 'amp-stories-fonts' );

/**
 * External dependencies
 */
import { render } from 'enzyme';

/**
 * Internal dependencies
 */
import FontFamilyPicker from '../';

describe( 'FontFamilyPicker', () => {
	it( 'should render a default button if no font is selected', () => {
		const fontFamilyPicker = render( <FontFamilyPicker /> );
		expect( fontFamilyPicker ).toMatchSnapshot();
	} );

	it( 'should render the selected font name', () => {
		const fontFamilyPicker = render( <FontFamilyPicker value="Arial" /> );
		expect( fontFamilyPicker ).toMatchSnapshot();
	} );

	it( 'should render the selected font svg preview', () => {
		const fontFamilyPicker = render( <FontFamilyPicker value="Roboto" /> );
		expect( fontFamilyPicker ).toMatchSnapshot();
	} );
} );
