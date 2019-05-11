/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * Internal dependencies
 */
import StoryControls from '../';

describe( 'StoryControls', () => {
	it( 'should render story controls', () => {
		const wrapper = shallow(
			<StoryControls />
		);

		expect( wrapper ).toMatchSnapshot();
	} );
} );
