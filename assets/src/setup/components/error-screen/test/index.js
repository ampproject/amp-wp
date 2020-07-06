
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
			<ErrorScreen finishLink={ 'http://my-exit-link.com' } error={ { message: 'The application failed' } } />,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
