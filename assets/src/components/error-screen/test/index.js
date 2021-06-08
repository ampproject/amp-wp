
/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { ErrorScreen } from '..';

describe( 'ErrorScreen', () => {
	it( 'matches snapshot', () => {
		const wrapper = create(
			<ErrorScreen
				finishLinkLabel="Go to homepage"
				finishLinkUrl="http://my-exit-link.com"
				error={ {
					message: 'The application failed',
					stack: 'ReferenceError: foo is not defined',
				} }
			/>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
