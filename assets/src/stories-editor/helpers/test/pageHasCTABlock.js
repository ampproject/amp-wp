/**
 * Internal dependencies
 */
import { pageHasCTABlock } from '../';

describe( 'maybeEnqueueFontStyle', () => {
	it( 'should return false if CTA block is not in the list', () => {
		expect( pageHasCTABlock( [] ) ).toEqual( false );
	} );
} );
