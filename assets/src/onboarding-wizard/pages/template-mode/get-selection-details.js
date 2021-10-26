/* eslint-disable complexity */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import { READER, STANDARD, TRANSITIONAL } from '../../../common/constants';

// Recommendation levels.
export const RECOMMENDED = 'recommended';
export const NEUTRAL = 'neutral';
export const NOT_RECOMMENDED = 'notRecommended';

// Technical levels.
export const TECHNICAL = 'technical';
export const NON_TECHNICAL = 'nonTechnical';

/**
 * Returns the degree to which each mode is recommended for the current site and user.
 *
 * @param {Object}  args
 * @param {boolean} args.currentThemeIsAmongReaderThemes  Whether the currently active theme is in the reader themes list.
 * @param {boolean} args.userIsTechnical                  Whether the user answered yes to the technical question.
 * @param {boolean} args.hasPluginsWithAMPIncompatibility Whether the site scan found plugins with AMP incompatibility.
 * @param {boolean} args.hasThemesWithAMPIncompatibility  Whether the site scan found themes with AMP incompatibility.
 * @param {boolean} args.hasScanResults                   Whether there are available scan results.
 */
export function getSelectionDetails( { currentThemeIsAmongReaderThemes, userIsTechnical, hasPluginsWithAMPIncompatibility, hasThemesWithAMPIncompatibility, hasScanResults = true } ) {
	// Handle case where scanning has failed or did not run.
	if ( ! hasScanResults ) {
		return {
			[ READER ]: {
				recommendationLevel: ( userIsTechnical || currentThemeIsAmongReaderThemes ) ? RECOMMENDED : NEUTRAL,
				details: [
					__( 'In Reader mode <strong>your site will have a non-AMP and an AMP version</strong>, and <strong>each version will use its own theme</strong>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
				],
			},
			[ TRANSITIONAL ]: {
				recommendationLevel: currentThemeIsAmongReaderThemes ? RECOMMENDED : NEUTRAL,
				details: [
					__( 'In Transitional mode <strong>your site will have a non-AMP and an AMP version</strong>, and <strong>both will use the same theme</strong>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
				],
			},
			[ STANDARD ]: {
				recommendationLevel: NEUTRAL,
				details: [
					__( 'In Standard mode <strong>your site will be completely AMP</strong> (except in cases where you opt-out of AMP for specific parts of your site), and <strong>it will use a single theme</strong>.', 'amp' ),
				],
			},
		};
	}

	switch ( true ) {
		case hasThemesWithAMPIncompatibility && hasPluginsWithAMPIncompatibility && userIsTechnical:
		case hasThemesWithAMPIncompatibility && ! hasPluginsWithAMPIncompatibility && userIsTechnical:
		case ! hasThemesWithAMPIncompatibility && hasPluginsWithAMPIncompatibility && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Possible choice if you want to enable AMP on your site despite the compatibility issues found.', 'amp' ),
						__( 'Your site will have <strong>non-AMP and AMP versions</strong>, each with its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Choose this mode temporarily if issues can be fixed or if your theme degrades gracefully when JavaScript is disabled.', 'amp' ),
						__( 'Your site will have <strong>non-AMP and AMP versions</strong> with the same theme.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<strong>Recommended</strong>, if you can fix the issues detected with plugins with and your theme.', 'amp' ),
						__( 'Your site will be completely AMP (except where you opt-out of AMP for specific areas), and will use a single theme.', 'amp' ),
					],
				},
			};

		case hasThemesWithAMPIncompatibility && hasPluginsWithAMPIncompatibility && ! userIsTechnical:
		case ! hasThemesWithAMPIncompatibility && hasPluginsWithAMPIncompatibility && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<strong>Recommended</strong> as an easy way to enable AMP on your site despite the issues detected during site scanning.', 'amp' ),
						__( 'Your site will have non-AMP and AMP versions, each using its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<strong>Not recommended</strong> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<strong>Not recommended</strong> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
			};

		case hasThemesWithAMPIncompatibility && ! hasPluginsWithAMPIncompatibility && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<strong>Recommended to easily enable AMP</strong> on your site despite the issues detected on your theme.', 'amp' ),
						__( 'Your site will have non-AMP and AMP versions, each using its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Choose this mode if your theme degrades gracefully when JavaScript is disabled.', 'amp' ),
						__( 'Your site will have <strong>non-AMP and AMP versions</strong> with the same theme.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<strong>Not recommended</strong> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
			};

		case ! hasThemesWithAMPIncompatibility && ! hasPluginsWithAMPIncompatibility && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<strong>Not recommended</strong> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<strong>Not recommended</strong> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<strong>Recommended</strong> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
			};

		case ! hasThemesWithAMPIncompatibility && ! hasPluginsWithAMPIncompatibility && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<strong>Not recommended</strong> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( '<strong>Recommended choice if you can’t commit</strong> to choosing plugins that are AMP compatible when extending your site. This mode will make it easy to keep AMP content even if non-AMP-compatible plugins are used later on.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( '<strong>Recommended choice if you can commit</strong> to always choosing plugins that are AMP compatible when extending your site.', 'amp' ),
					],
				},
			};

		default: {
			throw new Error( __( 'A template mode recommendation case was not accounted for.', 'amp' ) );
		}
	}
}

/* eslint-enable complexity */
