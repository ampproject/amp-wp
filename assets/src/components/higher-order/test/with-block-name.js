/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * Internal dependencies
 */
import withBlockName from '../with-block-name';

describe( 'withBlockName', () => {
	it( 'provides the the wrapped component with block name as props', () => {
		const EnhancedComponent = withBlockName( () => (
			<div />
		) );

		const wrapper = shallow(
			<EnhancedComponent />
		);

		expect( wrapper.dive() ).toMatchSnapshot();
	} );
} );
