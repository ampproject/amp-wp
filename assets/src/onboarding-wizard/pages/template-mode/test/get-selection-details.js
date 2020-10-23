/**
 * Internal dependencies
 */
import {
	getRecommendationLevels,
	getSelectionText,
	COMPATIBILITY,
	DETAILS,
	MOST_RECOMMENDED,
	NOT_RECOMMENDED,
	RECOMMENDED,
	TECHNICAL,
	NON_TECHNICAL,
} from '../get-selection-details';
import { READER, STANDARD, TRANSITIONAL } from '../../../../common/constants';

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

describe( 'getSelectionText', () => {
	it( 'throws no errors', () => {
		[ COMPATIBILITY, DETAILS ].forEach( ( section ) => {
			[ READER, STANDARD, TRANSITIONAL ].forEach( ( mode ) => {
				[ MOST_RECOMMENDED, NOT_RECOMMENDED, RECOMMENDED ].forEach( ( recommendationLevel ) => {
					[ NON_TECHNICAL, TECHNICAL ].forEach( ( technicalLevel ) => {
						const cb = () => getSelectionText( section, mode, recommendationLevel, technicalLevel );
						expect( cb ).not.toThrow();
					} );
				} );
			} );
		} );
	} );
} );
