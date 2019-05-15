/**
 * Internal dependencies
 */
import {
	getDefaultStatus,
	getPossibleStatuses,
} from '../selectors';

describe( 'selectors', () => {
	describe( 'getDefaultStatus', () => {
		it( 'should return the default AMP status', () => {
			const state = { defaultStatus: 'enabled' };

			expect( getDefaultStatus( state ) ).toEqual( 'enabled' );
		} );
	} );

	describe( 'getPossibleStatuses', () => {
		it( 'should return the possible AMP statuses', () => {
			const state = { possibleStatuses: [ 'enabled', 'disabled' ] };

			expect( getPossibleStatuses( state ) ).toEqual( [ 'enabled', 'disabled' ] );
		} );
	} );
} );
