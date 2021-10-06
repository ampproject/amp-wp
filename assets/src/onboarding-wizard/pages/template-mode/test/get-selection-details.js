/**
 * Internal dependencies
 */
import { getRecommendationLevels } from '../get-selection-details';

describe( 'getRecommendationLevels', () => {
	it( 'throws no errors', () => {
		[ true, false ].forEach( ( hasPluginIssues ) => {
			[ true, false ].forEach( ( hasThemeIssues ) => {
				[ true, false ].forEach( ( userIsTechnical ) => {
					const cb = () => getRecommendationLevels( { hasPluginIssues, hasThemeIssues, userIsTechnical } );
					expect( cb ).not.toThrow();
				} );
			} );
		} );
	} );
} );
