/**
 * Internal dependencies
 */
import { getTemplateModeRecommendation } from '../index';

describe( 'getTemplateModeRecommendation', () => {
	it( 'throws no errors', () => {
		[ true, false ].forEach( ( hasPluginIssues ) => {
			[ true, false ].forEach( ( hasThemeIssues ) => {
				[ true, false ].forEach( ( userIsTechnical ) => {
					const cb = () => getTemplateModeRecommendation( { hasPluginIssues, hasThemeIssues, userIsTechnical } );
					expect( cb ).not.toThrow();
				} );
			} );
		} );

		[ true, false ].forEach( ( hasFreshSiteScanResults ) => {
			[ true, false ].forEach( ( userIsTechnical ) => {
				const cb = () => getTemplateModeRecommendation( { userIsTechnical, hasFreshSiteScanResults } );
				expect( cb ).not.toThrow();
			} );
		} );
	} );
} );
