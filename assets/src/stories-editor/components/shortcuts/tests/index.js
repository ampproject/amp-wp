/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * Internal dependencies
 */
import Shortcuts from '../';

describe( 'Shortcuts', () => {
	it( 'should render block shortcuts', () => {
		const wrapper = shallow(
			<Shortcuts insertBlock={ jest.fn() } canInsertBlockType={ jest.fn().mockReturnValue( true ) } />
		);

		expect( wrapper ).toMatchSnapshot();
	} );
} );
