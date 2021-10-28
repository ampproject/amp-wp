/**
 * Internal dependencies
 */
import { getTemplateModeRecommendation } from '../index';

describe( 'getTemplateModeRecommendation', () => {
	it( 'throws no errors', () => {
		[ true, false ].forEach( ( hasPluginIssues ) => {
			[ true, false ].forEach( ( hasThemeIssues ) => {
				[ true, false ].forEach( ( userIsTechnical ) => {
					const cb = () => getTemplateModeRecommendation( { hasPluginIssues, hasThemeIssues, userIsTechnical, hasSiteScanResults: true } );
					expect( cb ).not.toThrow();
				} );
			} );
		} );
	} );
} );
