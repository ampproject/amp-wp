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

		[ true, false ].forEach( ( hasSiteScanResults ) => {
			[ true, false ].forEach( ( currentThemeIsAmongReaderThemes ) => {
				[ true, false ].forEach( ( userIsTechnical ) => {
					const cb = () => getTemplateModeRecommendation( { userIsTechnical, hasSiteScanResults, currentThemeIsAmongReaderThemes } );
					expect( cb ).not.toThrow();
				} );
			} );
		} );
	} );
} );
