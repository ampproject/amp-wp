/* eslint-disable complexity */
/* eslint-disable jsdoc/check-param-names */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import isShallowEqual from '@wordpress/is-shallow-equal';
/**
 * Internal dependencies
 */
import { READER, STANDARD, TRANSITIONAL } from '../../../common/constants';

// Sections.
export const COMPATIBILITY = 'compatibility';
export const DETAILS = 'details';

// Recommendation levels.
export const MOST_RECOMMENDED = 'mostRecommended';
export const NOT_RECOMMENDED = 'notRecommended';
export const RECOMMENDED = 'recommended';

// Technical levels.
export const TECHNICAL = 'technical';
export const NON_TECHNICAL = 'nonTechnical';

/**
 * Returns the degree to which each mode is recommended for the current site and user.
 *
 * @param {Object}  args
 * @param {boolean} args.currentThemeIsAmongReaderThemes Whether the currently active theme is in the reader themes list.
 * @param {boolean} args.userIsTechnical                 Whether the user answered yes to the technical question.
 * @param {boolean} args.hasPluginIssues                 Whether the site scan found plugin issues.
 * @param {boolean} args.hasThemeIssues                  Whether the site scan found theme issues.
 * @param {boolean} args.hasScanResults                  Whether there are available scan results.
 */
export function getRecommendationLevels( { currentThemeIsAmongReaderThemes, userIsTechnical, hasPluginIssues, hasThemeIssues, hasScanResults = true } ) {
	// Handle case where scanning has failed or did not run.
	if ( ! hasScanResults ) {
		if ( userIsTechnical ) {
			return {
				[ READER ]: currentThemeIsAmongReaderThemes ? MOST_RECOMMENDED : RECOMMENDED,
				[ STANDARD ]: RECOMMENDED,
				[ TRANSITIONAL ]: currentThemeIsAmongReaderThemes ? MOST_RECOMMENDED : RECOMMENDED,
			};
		}
		return {
			[ READER ]: MOST_RECOMMENDED,
			[ STANDARD ]: RECOMMENDED,
			[ TRANSITIONAL ]: currentThemeIsAmongReaderThemes ? MOST_RECOMMENDED : RECOMMENDED,
		};
	}

	switch ( true ) {
		case hasThemeIssues && hasPluginIssues && userIsTechnical:
		case hasThemeIssues && ! hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: MOST_RECOMMENDED,
				[ STANDARD ]: NOT_RECOMMENDED,
				[ TRANSITIONAL ]: RECOMMENDED,
			};

		case hasThemeIssues && hasPluginIssues && ! userIsTechnical:
		case hasThemeIssues && ! hasPluginIssues && ! userIsTechnical:
			return {
				[ READER ]: MOST_RECOMMENDED,
				[ STANDARD ]: NOT_RECOMMENDED,
				[ TRANSITIONAL ]: NOT_RECOMMENDED,
			};

		case ! hasThemeIssues && hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: NOT_RECOMMENDED,
				[ STANDARD ]: NOT_RECOMMENDED,
				[ TRANSITIONAL ]: MOST_RECOMMENDED,
			};

		case ! hasThemeIssues && hasPluginIssues && ! userIsTechnical:
			return {
				[ READER ]: NOT_RECOMMENDED,
				[ STANDARD ]: NOT_RECOMMENDED,
				[ TRANSITIONAL ]: MOST_RECOMMENDED,
			};

		case ! hasThemeIssues && ! hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: NOT_RECOMMENDED,
				[ STANDARD ]: MOST_RECOMMENDED,
				[ TRANSITIONAL ]: NOT_RECOMMENDED,
			};

		case ! hasThemeIssues && ! hasPluginIssues && ! userIsTechnical:
			return {
				[ READER ]: NOT_RECOMMENDED,
				[ STANDARD ]: NOT_RECOMMENDED,
				[ TRANSITIONAL ]: MOST_RECOMMENDED,
			};

		default: {
			throw new Error( __( 'A template mode recommendation case was not accounted for.', 'amp' ) );
		}
	}
}

/**
 * Provides details on copy and UI for the template modes screen.
 *
 * @param {Array}  args                Function args.
 * @param {string} section             The section for which to provide text.
 * @param {string} mode                The mode to generate text for.
 * @param {string} recommendationLevel String representing whether the mode is not recommended, recommended, or most recommended.
 * @param {string} technicalLevel      String representing whether the user is technical.
 */
export function getSelectionText( ...args ) {
	const match = ( ...test ) => isShallowEqual( test, args );

	switch ( true ) {
		case match( COMPATIBILITY, READER, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, READER, MOST_RECOMMENDED, TECHNICAL ):
			return __( 'Reader mode is the best choice if you don\'t have a technical background or would like a simpler setup.', 'amp' );

		case match( COMPATIBILITY, READER, RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, READER, RECOMMENDED, TECHNICAL ):
			return __( 'Reader mode makes it easy to bring AMP content to your site, but your site will use two different themes.', 'amp' );

		case match( COMPATIBILITY, READER, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, READER, NOT_RECOMMENDED, TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, NOT_RECOMMENDED, TECHNICAL ):
			return __( 'There is no reason to use this mode, as you have an AMP-compatible theme that you can use for both the non-AMP and AMP versions of your site.', 'amp' );

		case match( COMPATIBILITY, STANDARD, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, STANDARD, NOT_RECOMMENDED, TECHNICAL ):
			return __( 'Standard mode is not recommended as key functionality may be missing and development work might be required. ', 'amp' );

		case match( COMPATIBILITY, STANDARD, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, STANDARD, MOST_RECOMMENDED, TECHNICAL ):
			return __( 'Standard mode is the best choice for your site because you are using an AMP-compatible theme and no plugin issues were detected.', 'amp' );

		case match( COMPATIBILITY, TRANSITIONAL, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, MOST_RECOMMENDED, TECHNICAL ):
			return __( 'Transitional mode is recommended because it makes it easy to keep your content as valid AMP even if non-AMP-compatible plugins are installed later.', 'amp' );

		case match( COMPATIBILITY, TRANSITIONAL, RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, RECOMMENDED, TECHNICAL ):
			return __( 'Transitional mode is a good choice if you are willing and able to address any issues around AMP-compatibility that may arise as your site evolves.', 'amp' );

		case match( DETAILS, READER, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, READER, NOT_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, READER, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, READER, MOST_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, READER, RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, READER, RECOMMENDED, TECHNICAL ):
			return __( 'In Reader mode <b>your site will have a non-AMP and an AMP version</b>, and <b>each version will use its own theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' );

		case match( DETAILS, TRANSITIONAL, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, NOT_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, MOST_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, RECOMMENDED, TECHNICAL ):
			return __( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content. ', 'amp' );

		case match( DETAILS, STANDARD, RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, STANDARD, RECOMMENDED, TECHNICAL ):
		case match( DETAILS, STANDARD, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, STANDARD, MOST_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, STANDARD, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, STANDARD, NOT_RECOMMENDED, TECHNICAL ):
			return __( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific parts of your site), and <b>it will use a single theme</b>. ', 'amp' );

		// Cases potentially never used.
		case match( COMPATIBILITY, STANDARD, RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, STANDARD, RECOMMENDED, TECHNICAL ):
			return 'Standard mode is a good choice if your site uses an AMP-compatible theme and only uses AMP-compatible plugins. If you\'re not sure of the compatibility of your themes and plugins, Reader mode may be a better option.';

		default: {
			throw new Error( __( 'A selection text recommendation was not accounted for. ', 'amp' ) + JSON.stringify( args ) );
		}
	}
}

/**
 * Gets all the selection text for the ScreenUI component.
 *
 * @param {Object} recommendationLevels Result of getRecommendationLevels.
 * @param {string} technicalLevel       A technical level.
 */
export function getAllSelectionText( recommendationLevels, technicalLevel ) {
	return {
		[ READER ]: {
			[ COMPATIBILITY ]: getSelectionText( COMPATIBILITY, READER, recommendationLevels[ READER ], technicalLevel ),
			[ DETAILS ]: getSelectionText( DETAILS, READER, recommendationLevels[ READER ], technicalLevel ),
		},
		[ STANDARD ]: {
			[ COMPATIBILITY ]: getSelectionText( COMPATIBILITY, STANDARD, recommendationLevels[ STANDARD ], technicalLevel ),
			[ DETAILS ]: getSelectionText( DETAILS, STANDARD, recommendationLevels[ STANDARD ], technicalLevel ),
		},
		[ TRANSITIONAL ]: {
			[ COMPATIBILITY ]: getSelectionText( COMPATIBILITY, TRANSITIONAL, recommendationLevels[ TRANSITIONAL ], technicalLevel ),
			[ DETAILS ]: getSelectionText( DETAILS, TRANSITIONAL, recommendationLevels[ TRANSITIONAL ], technicalLevel ),
		},
	};
}

/* eslint-enable complexity */
/* eslint-enable jsdoc/check-param-names */
