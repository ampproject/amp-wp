
/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { TechnicalBackground } from '..';
import { Providers } from '../../..';

describe( 'TechnicalBackground', () => {
	it( 'matches snapshot', () => {
		const wrapper = create(
			<Providers>
				<TechnicalBackground />
			</Providers>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
