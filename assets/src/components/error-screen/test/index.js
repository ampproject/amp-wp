
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
				finishLink={ {
					url: 'http://my-exit-link.com',
					label: 'Go to homepage',
				} }
				error={ {
					message: 'The application failed',
					stack: 'ReferenceError: foo is not defined',
				} }
			/>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
