/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useLayoutEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { READER, STANDARD, TRANSITIONAL } from '../../common/constants';
import { ReaderThemes } from '../reader-themes-context-provider';
import { SiteScan as SiteScanContext } from '../site-scan-context-provider';
import { User } from '../user-context-provider';
import { Options } from '../options-context-provider';

// Recommendation levels.
export const RECOMMENDED = 'recommended';
export const NEUTRAL = 'neutral';
export const NOT_RECOMMENDED = 'notRecommended';

// Technical levels.
export const TECHNICAL = 'technical';
export const NON_TECHNICAL = 'nonTechnical';

export function useTemplateModeRecommendation() {
	const { currentTheme: { is_reader_theme: currentThemeIsAmongReaderThemes } } = useContext( ReaderThemes );
	const {
		hasSiteScanResults,
		isBusy,
		isFetchingScannableUrls,
		pluginsWithAmpIncompatibility,
		stale,
		themesWithAmpIncompatibility,
	} = useContext( SiteScanContext );
	const { originalDeveloperToolsOption } = useContext( User );
	const { fetchingOptions, savingOptions } = useContext( Options );
	const [ templateModeRecommendation, setTemplateModeRecommendation ] = useState( null );

	useLayoutEffect( () => {
		if ( isBusy || isFetchingScannableUrls || fetchingOptions || savingOptions ) {
			return;
		}

		setTemplateModeRecommendation( getTemplateModeRecommendation( {
			currentThemeIsAmongReaderThemes,
			hasPluginIssues: pluginsWithAmpIncompatibility?.length > 0,
			hasSiteScanResults: hasSiteScanResults && ! stale,
			hasThemeIssues: themesWithAmpIncompatibility?.length > 0,
			userIsTechnical: originalDeveloperToolsOption === true,
		} ) );
	}, [ currentThemeIsAmongReaderThemes, fetchingOptions, hasSiteScanResults, isBusy, isFetchingScannableUrls, originalDeveloperToolsOption, pluginsWithAmpIncompatibility?.length, savingOptions, stale, themesWithAmpIncompatibility?.length ] );

	return {
		templateModeRecommendation,
		staleTemplateModeRecommendation: stale,
	};
}

/* eslint-disable complexity */

/**
 * Returns the degree to which each mode is recommended for the current site and user.
 *
 * @param {Object}  args
 * @param {boolean} args.currentThemeIsAmongReaderThemes Whether the currently active theme is in the reader themes list.
 * @param {boolean} args.hasPluginIssues                 Whether the site scan found plugins with AMP incompatibility.
 * @param {boolean} args.hasSiteScanResults              Whether there are available site scan results.
 * @param {boolean} args.hasThemeIssues                  Whether the site scan found themes with AMP incompatibility.
 * @param {boolean} args.userIsTechnical                 Whether the user answered yes to the technical question.
 */
export function getTemplateModeRecommendation( {
	currentThemeIsAmongReaderThemes,
	hasPluginIssues,
	hasSiteScanResults,
	hasThemeIssues,
	userIsTechnical,
} ) {
	switch ( true ) {
		/**
		 * #1
		 */
		case hasThemeIssues && hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Possible choice if you want to enable AMP on your site despite the compatibility issues found.', 'amp' ),
						__( 'Your site will have <b>non-AMP and AMP versions</b>, each with its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Choose this mode temporarily if issues can be fixed or if your theme degrades gracefully when JavaScript is disabled.', 'amp' ),
						__( 'Your site will have <b>non-AMP and AMP versions</b> with the same theme.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<b>Recommended</b>, if you can fix the issues detected with plugins with and your theme.', 'amp' ),
						__( 'Your site will be completely AMP (except where you opt-out of AMP for specific areas), and will use a single theme.', 'amp' ),
					],
				},
			};

		/**
		 * #2
		 */
		case hasThemeIssues && hasPluginIssues && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<b>Recommended</b> as an easy way to enable AMP on your site despite the issues detected during site scanning.', 'amp' ),
						__( 'Your site will have non-AMP and AMP versions, each using its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
			};

		/**
		 * #3
		 */
		case hasThemeIssues && ! hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Possible choice if you want to enable AMP on your site despite the compatibility issues found.', 'amp' ),
						__( 'Your site will have <b>non-AMP and AMP versions</b>, each with its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Choose this mode temporarily if issues can be fixed or if your theme degrades gracefully when JavaScript is disabled.', 'amp' ),
						__( 'Your site will have <b>non-AMP and AMP versions</b> with the same theme.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<b>Recommended</b>, if you can fix the issues detected with plugins with and your theme.', 'amp' ),
						__( 'Your site will be completely AMP (except where you opt-out of AMP for specific areas), and will use a single theme.', 'amp' ),
					],
				},
			};

		/**
		 * #4
		 */
		case hasThemeIssues && ! hasPluginIssues && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<b>Recommended to easily enable AMP</b> on your site despite the issues detected on your theme.', 'amp' ),
						__( 'Your site will have non-AMP and AMP versions, each using its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Choose this mode if your theme degrades gracefully when JavaScript is disabled.', 'amp' ),
						__( 'Your site will have <b>non-AMP and AMP versions</b> with the same theme.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
			};

		/**
		 * #5
		 */
		case ! hasThemeIssues && hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Possible choice if you want to enable AMP on your site despite the compatibility issues found.', 'amp' ),
						__( 'Your site will have <b>non-AMP and AMP versions</b>, each with its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'Choose this mode temporarily if issues can be fixed or if your theme degrades gracefully when JavaScript is disabled.', 'amp' ),
						__( 'Your site will have <b>non-AMP and AMP versions</b> with the same theme.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<b>Recommended</b>, if you can fix the issues detected with plugins with and your theme.', 'amp' ),
						__( 'Your site will be completely AMP (except where you opt-out of AMP for specific areas), and will use a single theme.', 'amp' ),
					],
				},
			};

		/**
		 * #6
		 */
		case ! hasThemeIssues && hasPluginIssues && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<b>Recommended</b> as an easy way to enable AMP on your site despite the issues detected during site scanning.', 'amp' ),
						__( 'Your site will have non-AMP and AMP versions, each using its own theme.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as key functionality may be missing and development work might be required.', 'amp' ),
					],
				},
			};

		/**
		 * #7
		 */
		case ! hasThemeIssues && ! hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( '<b>Recommended</b> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
			};

		/**
		 * #8
		 */
		case ! hasThemeIssues && ! hasPluginIssues && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						__( '<b>Not recommended</b> as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( '<b>Recommended choice if you can’t commit</b> to choosing plugins that are AMP compatible when extending your site. This mode will make it easy to keep AMP content even if non-AMP-compatible plugins are used later on.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( '<b>Recommended choice if you can commit</b> to always choosing plugins that are AMP compatible when extending your site.', 'amp' ),
					],
				},
			};

		/**
		 * No site scan scenarios.
		 */
		case ! hasSiteScanResults && currentThemeIsAmongReaderThemes && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( 'In Reader mode <b>your site will have a non-AMP and an AMP version</b>, and <b>each version will use its own theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific parts of your site), and <b>it will use a single theme</b>.', 'amp' ),
					],
				},
			};

		case ! hasSiteScanResults && currentThemeIsAmongReaderThemes && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( 'In Reader mode <b>your site will have a non-AMP and an AMP version</b>, and <b>each version will use its own theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific parts of your site), and <b>it will use a single theme</b>.', 'amp' ),
					],
				},
			};

		case ! hasSiteScanResults && ! currentThemeIsAmongReaderThemes && ! userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'In Reader mode <b>your site will have a non-AMP and an AMP version</b>, and <b>each version will use its own theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific parts of your site), and <b>it will use a single theme</b>.', 'amp' ),
					],
				},
			};

		case ! hasSiteScanResults && ! currentThemeIsAmongReaderThemes && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						__( 'In Reader mode <b>your site will have a non-AMP and an AMP version</b>, and <b>each version will use its own theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						__( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific parts of your site), and <b>it will use a single theme</b>.', 'amp' ),
					],
				},
			};

		default:
			throw new Error( __( 'A template mode recommendation case was not accounted for.', 'amp' ) );
	}
}

/* eslint-enable complexity */
