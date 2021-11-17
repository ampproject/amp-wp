/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useLayoutEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { READER, STANDARD, TRANSITIONAL } from '../../common/constants';
import { SiteScan } from '../site-scan-context-provider';
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
	const {
		hasSiteScanResults,
		isBusy,
		isFetchingScannableUrls,
		pluginsWithAmpIncompatibility,
		stale,
		themesWithAmpIncompatibility,
	} = useContext( SiteScan );
	const { developerToolsOption, fetchingUser, savingDeveloperToolsOption } = useContext( User );
	const { fetchingOptions, savingOptions } = useContext( Options );
	const [ templateModeRecommendation, setTemplateModeRecommendation ] = useState( null );

	useLayoutEffect( () => {
		if ( isBusy || isFetchingScannableUrls || fetchingOptions || savingOptions || fetchingUser || savingDeveloperToolsOption ) {
			return;
		}

		setTemplateModeRecommendation( getTemplateModeRecommendation( {
			hasPluginIssues: pluginsWithAmpIncompatibility?.length > 0,
			hasFreshSiteScanResults: hasSiteScanResults && ! stale,
			hasThemeIssues: themesWithAmpIncompatibility?.length > 0,
			userIsTechnical: developerToolsOption === true,
		} ) );
	}, [ developerToolsOption, fetchingOptions, fetchingUser, hasSiteScanResults, isBusy, isFetchingScannableUrls, pluginsWithAmpIncompatibility?.length, savingDeveloperToolsOption, savingOptions, stale, themesWithAmpIncompatibility?.length ] );

	return templateModeRecommendation;
}

/* eslint-disable complexity */

/**
 * Returns the degree to which each mode is recommended for the current site and user.
 *
 * @param {Object}  args
 * @param {boolean} args.hasPluginIssues         Whether the site scan found plugins with AMP incompatibility.
 * @param {boolean} args.hasFreshSiteScanResults Whether fresh site scan results are available.
 * @param {boolean} args.hasThemeIssues          Whether the site scan found themes with AMP incompatibility.
 * @param {boolean} args.userIsTechnical         Whether the user answered yes to the technical question.
 */
export function getTemplateModeRecommendation( {
	hasPluginIssues,
	hasFreshSiteScanResults,
	hasThemeIssues,
	userIsTechnical,
} ) {
	/* eslint-disable @wordpress/no-unused-vars-before-return */
	const mobileRedirectionNote = __( 'If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' );
	const readerModeDescription = __( 'In Reader mode <b>your site will have a non-AMP and an AMP version</b>, and <b>each version will use its own theme</b>.', 'amp' ) + ' ' + mobileRedirectionNote;
	const transitionalModeDescription = __( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>.', 'amp' ) + ' ' + mobileRedirectionNote;
	const standardModeDescription = __( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific parts of your site), and <b>it will use a single theme</b>.', 'amp' );
	const pluginIncompatibilityNote = __( 'To address plugin compatibility issue(s), you may need to use Plugin Suppression to disable incompatible plugins on AMP pages or else select an alternative AMP-compatible plugin.', 'amp' );
	const readerNoteWhenThemeIssuesPresent = __( 'Recommended if you want to enable AMP on your site despite the detected compatibility issue(s).', 'amp' );
	const transitionalNoteWhenThemeIssuesPresent = __( 'Recommended so you can progressively enable AMP on your site while still making the non-AMP version available to visitors for functionality that is not AMP-compatible. Choose this mode if compatibility issues can be fixed or if your theme degrades gracefully when JavaScript is disabled.', 'amp' );
	const notRecommendedDueToCompleteCompatibility = __( 'Not recommended as your site has no AMP compatibility issues detected.', 'amp' );
	const notRecommendedUntilIncompatibilitiesFixed = __( 'Not recommended until you can fix the detected compatibility issue(s).', 'amp' );
	const recommendedDueToNoThemeIncompatibilities = __( 'Recommended since there were no theme compatibility issues detected.', 'amp' );
	const notRecommendedDueToIncompatibilities = __( 'Not recommended due to compatibility issue(s) which may break key site functionality, without developer assistance.', 'amp' );
	/* eslint-enable @wordpress/no-unused-vars-before-return */

	switch ( true ) {
		/**
		 * No site scan results or stale results.
		 */
		case ! hasFreshSiteScanResults:
			return {
				[ READER ]: {
					recommendationLevel: NEUTRAL,
					details: [
						readerModeDescription,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						transitionalModeDescription,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						standardModeDescription,
					],
				},
			};

		/**
		 * #1
		 */
		case hasThemeIssues && hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						readerModeDescription,
						readerNoteWhenThemeIssuesPresent,
						pluginIncompatibilityNote,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						transitionalModeDescription,
						transitionalNoteWhenThemeIssuesPresent,
						pluginIncompatibilityNote,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						standardModeDescription,
						notRecommendedUntilIncompatibilitiesFixed,
						pluginIncompatibilityNote,
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
						readerModeDescription,
						readerNoteWhenThemeIssuesPresent,
						pluginIncompatibilityNote,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						transitionalModeDescription,
						notRecommendedDueToIncompatibilities,
						pluginIncompatibilityNote,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						standardModeDescription,
						notRecommendedDueToIncompatibilities,
						pluginIncompatibilityNote,
					],
				},
			};

		/**
		 * #3
		 */
		case hasThemeIssues && ! hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						readerModeDescription,
						readerNoteWhenThemeIssuesPresent,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						transitionalModeDescription,
						transitionalNoteWhenThemeIssuesPresent,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						standardModeDescription,
						notRecommendedUntilIncompatibilitiesFixed,
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
						readerModeDescription,
						readerNoteWhenThemeIssuesPresent,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						transitionalModeDescription,
						notRecommendedDueToIncompatibilities,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						standardModeDescription,
						notRecommendedDueToIncompatibilities,
					],
				},
			};

		/**
		 * #5
		 */
		case ! hasThemeIssues && hasPluginIssues && userIsTechnical:
			return {
				[ READER ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						readerModeDescription,
						pluginIncompatibilityNote,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						transitionalModeDescription,
						recommendedDueToNoThemeIncompatibilities,
						pluginIncompatibilityNote,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						standardModeDescription,
						notRecommendedUntilIncompatibilitiesFixed,
						pluginIncompatibilityNote,
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
						readerModeDescription,
						pluginIncompatibilityNote,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						transitionalModeDescription,
						recommendedDueToNoThemeIncompatibilities,
						pluginIncompatibilityNote,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						standardModeDescription,
						notRecommendedDueToIncompatibilities,
						pluginIncompatibilityNote,
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
						readerModeDescription,
						notRecommendedDueToCompleteCompatibility,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NOT_RECOMMENDED,
					details: [
						transitionalModeDescription,
						notRecommendedDueToCompleteCompatibility,
					],
				},
				[ STANDARD ]: {
					recommendationLevel: RECOMMENDED,
					details: [
						standardModeDescription,
						__( 'Recommended as you have an AMP-compatible theme and no issues were detected with any of the plugins on your site.', 'amp' ),
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
						readerModeDescription,
						notRecommendedDueToCompleteCompatibility,
					],
				},
				[ TRANSITIONAL ]: {
					recommendationLevel: NEUTRAL,
					details: [
						transitionalModeDescription,
						__( 'Recommended if you can’t commit to choosing plugins that are AMP compatible when extending your site. This mode will make it easy to keep AMP content even if non-AMP-compatible plugins are used later on.', 'amp' ),
					],
				},
				[ STANDARD ]: {
					recommendationLevel: NEUTRAL,
					details: [
						standardModeDescription,
						__( 'Recommended if you can commit to always choosing plugins that are AMP compatible when extending your site.', 'amp' ),
					],
				},
			};

		default:
			throw new Error( __( 'A template mode recommendation case was not accounted for.', 'amp' ) );
	}
}

/* eslint-enable complexity */
