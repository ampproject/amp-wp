
/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import ClipboardButton from '..';

describe( 'ClipboardButton', () => {
	it( 'matches snapshot', () => {
		const wrapper = create(
			<ClipboardButton text="Sample text" />,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
