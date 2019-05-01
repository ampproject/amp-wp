/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * Internal dependencies
 */
import withIsReordering from '../with-is-reordering';

describe( 'withIsReordering', () => {
	it( 'provides the the wrapped component with isReordering state as props', () => {
		const EnhancedComponent = withIsReordering( () => (
			<div />
		) );

		const wrapper = shallow(
			<EnhancedComponent />
		);

		expect( wrapper.dive() ).toMatchSnapshot();
	} );
} );
