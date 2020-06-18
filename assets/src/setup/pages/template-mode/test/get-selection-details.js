/**
 * Internal dependencies
 */
import { getSelectionDetails } from '../get-selection-details';

describe( 'getSelectionDetails', () => {
	it( 'throws no errors', () => {
		[ true, false ].forEach( ( hasPluginIssues ) => {
			[ true, false ].forEach( ( hasThemeIssues ) => {
				[ true, false ].forEach( ( userIsTechnical ) => {
					const cb = () => getSelectionDetails( { hasPluginIssues, hasThemeIssues, userIsTechnical } );
					expect( cb ).not.toThrow();
				} );
			} );
		} );
	} );
} );
