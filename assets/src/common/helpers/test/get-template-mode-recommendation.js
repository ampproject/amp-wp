/**
 * Internal dependencies
 */
import { getTemplateModeRecommendation } from '../get-template-mode-recommendation';

describe( 'getTemplateModeRecommendation', () => {
	it( 'throws no errors', () => {
		[ true, false ].forEach( ( hasPluginsWithAmpIncompatibility ) => {
			[ true, false ].forEach( ( hasThemesWithAmpIncompatibility ) => {
				[ true, false ].forEach( ( userIsTechnical ) => {
					const cb = () => getTemplateModeRecommendation( { hasPluginsWithAmpIncompatibility, hasThemesWithAmpIncompatibility, userIsTechnical } );
					expect( cb ).not.toThrow();
				} );
			} );
		} );
	} );
} );
