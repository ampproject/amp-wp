/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * Internal dependencies
 */
import EditorCarousel from '../';

describe( 'EditorCarousel', () => {
	it( 'should render block shortcuts', () => {
		const wrapper = shallow(
			<EditorCarousel />
		);

		expect( wrapper ).toMatchSnapshot();
	} );
} );
