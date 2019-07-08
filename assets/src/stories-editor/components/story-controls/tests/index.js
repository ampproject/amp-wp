/**
 * External dependencies
 */
import { render } from 'enzyme';

/**
 * Internal dependencies
 */
import StoryControls from '../';

describe( 'StoryControls', () => {
	it( 'should render story controls', () => {
		const wrapper = render(
			<StoryControls />
		);

		expect( wrapper ).toMatchSnapshot();
	} );
} );
