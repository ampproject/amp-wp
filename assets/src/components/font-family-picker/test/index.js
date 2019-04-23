jest.mock( 'amp-stories-fonts' );

/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * Internal dependencies
 */
import FontFamilyPicker from '../';

describe( 'FontFamilyPicker', () => {
	describe( 'basic rendering', () => {
		it( 'should render an empty null element when list of fonts is not provided', () => {
			const fontFamilyPicker = shallow( <FontFamilyPicker /> );
			// Unrendered element.
			expect( fontFamilyPicker.type() ).toBeNull();
		} );
	} );
} );
