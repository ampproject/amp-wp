/* eslint-disable complexity */
/* eslint-disable jsdoc/check-param-names */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import isShallowEqual from '@wordpress/is-shallow-equal';

// Sections.
export const COMPATIBILITY = 'compatibility';
export const DETAILS = 'details';

// Modes.
export const READER = 'reader';
export const STANDARD = 'standard';
export const TRANSITIONAL = 'transitional';

// Recommendation levels.
export const MOST_RECOMMENDED = 'mostRecommended';
export const NOT_RECOMMENDED = 'notRecommended';
export const RECOMMENDED = 'recommended';

// Technical levels.
export const TECHNICAL = 'technical';
export const NON_TECHNICAL = 'nonTechnical';

export function getRecommendationLevels( { userIsTechnical, hasPluginIssues, hasThemeIssues } ) {
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
 * @param {Array} args Function args.
 * @param {string} section The section for which to provide text.
 * @param {string} mode The mode to generate text for.
 * @param {string} recommendationLevel String representing whether the mode is not recommended, recommended, or most recommended.
 * @param {string} technicalLevel String representing whether the user is technical.
 */
export function getSelectionText( ...args ) {
	const match = ( ...test ) => isShallowEqual( test, args );

	switch ( true ) {
		case match( COMPATIBILITY, READER, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, READER, MOST_RECOMMENDED, TECHNICAL ):
			return __( 'Best choice that makes it easy to turn AMP on despite the issues detected during the site scan', 'amp' );

		case match( COMPATIBILITY, READER, RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, READER, RECOMMENDED, TECHNICAL ):
			return __( 'Acceptable choice that makes it easy to bring AMP content to your site, but this mode requires you to maintain two versions of your site.', 'amp' );

		case match( COMPATIBILITY, READER, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, READER, NOT_RECOMMENDED, TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, NOT_RECOMMENDED, TECHNICAL ):
			return __( 'There is no reason to use this mode, as you have an AMP-compatible theme that you can use for both the non-AMP and AMP versions of your site.', 'amp' );

		case match( COMPATIBILITY, STANDARD, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, STANDARD, NOT_RECOMMENDED, TECHNICAL ):
			return __( 'Not recommended as key functionality may be missing and development work might be required. ', 'amp' );

		case match( COMPATIBILITY, STANDARD, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, STANDARD, MOST_RECOMMENDED, TECHNICAL ):
			return __( 'This is a good choice since you have an AMP-compatible theme and no plugin issues were detected.', 'amp' );

		case match( COMPATIBILITY, TRANSITIONAL, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, MOST_RECOMMENDED, TECHNICAL ):
			return __( 'Recommended choice. It will make it easy to keep AMP content even if non-AMP-compatible plugins are used later on.', 'amp' );

		case match( COMPATIBILITY, TRANSITIONAL, RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, TRANSITIONAL, RECOMMENDED, TECHNICAL ):
			return __( 'A good choice if development work is pursued to fix the issues for critical functionality', 'amp' );

		case match( DETAILS, READER, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, READER, NOT_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, READER, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, READER, MOST_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, READER, RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, READER, RECOMMENDED, TECHNICAL ):
			return __( 'In reader mode there are two themes and two versions of your site. It is the best choice if your site uses a theme that is not compatible with AMP (i.e. critical functionality is powered by JavaScript or it uses excessive CSS). With this mode you can keep your active theme, and bring AMP to your content strategy using any AMP-first theme.', 'amp' );

		case match( DETAILS, TRANSITIONAL, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, NOT_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, MOST_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, TRANSITIONAL, RECOMMENDED, TECHNICAL ):
			return __( 'A single theme and two versions of your full site. A plausible choice if your site\'s theme is only partially AMP compatible. If you are a power user, you can leverage the transitional mode while you work towards making the site fully AMP compatible.', 'amp' );

		case match( DETAILS, STANDARD, RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, STANDARD, RECOMMENDED, TECHNICAL ):
		case match( DETAILS, STANDARD, MOST_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, STANDARD, MOST_RECOMMENDED, TECHNICAL ):
		case match( DETAILS, STANDARD, NOT_RECOMMENDED, NON_TECHNICAL ):
		case match( DETAILS, STANDARD, NOT_RECOMMENDED, TECHNICAL ):
			return __( 'In Standard Mode your site uses a single theme and there is a single version of your content. In this mode, AMP is the framework of your site and there is reduced development and maintenance costs by having a single site to maintain.', 'amp' );

		// Cases potentially never used.
		case match( COMPATIBILITY, STANDARD, RECOMMENDED, NON_TECHNICAL ):
		case match( COMPATIBILITY, STANDARD, RECOMMENDED, TECHNICAL ):
			return '';

		default: {
			throw new Error( __( 'A selection text recommentation was not accounted for. ', 'amp' ) + JSON.stringify( args ) );
		}
	}
}

/**
 * Gets all the selection text for the ScreenUI component.
 *
 * @param {Object} recommendationLevels Result of getRecommendationLevels.
 * @param {string} technicalLevel A technical level.
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
