/* eslint-disable complexity */
/* eslint-disable @wordpress/no-unused-vars-before-return */
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

export const MOST_RECOMMENDED = 'most-recommended';
export const RECOMMENDED = 'recommended';
export const LEAST_RECOMMENDED = 'least-recommended';

/**
 * Provides details on copy and UI for the template modes screen.
 *
 * @param {Object} arguments
 * @param {boolean} arguments.userIsTechnical Whether the user has dev tools enabled.
 * @param {boolean} arguments.hasPluginIssues Whether plugin issues were found.
 * @param {boolean} arguments.hasThemeIssues Whether theme issues were found.
 * @return {Object} Information for UI display.
 */
export function getSelectionDetails( { userIsTechnical, hasPluginIssues, hasThemeIssues } ) {
	const READER_DO_NOT_USE = __( 'There is no reason to use this mode, as you have an AMP-compatible theme that you can use for both the non-AMP and AMP versions of your site.', 'amp' );
	const STANDARD_TECHNICAL_DO_NOT_USE = __( 'There is no reason to use this mode, as you have an AMP-compatible theme that you can use for both the non-AMP and AMP versions of your site.', 'amp' );
	const TRANSITIONAL_DO_NOT_USE = __( 'There is no reason to use this mode, as you have an AMP-compatible theme that you can use for both the non-AMP and AMP versions of your site.', 'amp' );
	const STANDARD_NONTECHNICAL_DO_NOT_USE = __( 'Not recommended as key functionality may be missing and development work might be required. ', 'amp' );

	// Numbers are rows in the "AMP Copy Configuration Table" Google doc.
	switch ( true ) {
		case hasThemeIssues && hasPluginIssues && userIsTechnical: // 1
		case hasThemeIssues && ! hasPluginIssues && userIsTechnical: // 3
			return {
				reader: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						__( 'Lorem ipsum', 'amp' ),
						__( 'Lorem ipsum', 'amp' ),
					],
					recommended: MOST_RECOMMENDED,
				},
				standard: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						STANDARD_TECHNICAL_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				transitional: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						__( 'Lorem ipsum', 'amp' ),
						__( 'Lorem ipsum', 'amp' ),
					],
					recommended: RECOMMENDED,
				},
			};

		case hasThemeIssues && hasPluginIssues && ! userIsTechnical: // 2
		case hasThemeIssues && ! hasPluginIssues && ! userIsTechnical: // 4
			return {
				reader: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						__( 'Lorem ipsum', 'amp' ),
						__( 'Lorem ipsum', 'amp' ),
					],
					recommended: MOST_RECOMMENDED,
				},
				standard: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						STANDARD_NONTECHNICAL_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				transitional: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						TRANSITIONAL_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
			};

		case ! hasThemeIssues && hasPluginIssues && userIsTechnical: // 5
			return {
				reader: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						READER_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				standard: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						STANDARD_TECHNICAL_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				transitional: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						__( 'Lorem ipsum', 'amp' ),
					],
					recommended: MOST_RECOMMENDED,
				},
			};

		case ! hasThemeIssues && hasPluginIssues && ! userIsTechnical: // 6
			return {
				reader: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						READER_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				standard: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						STANDARD_NONTECHNICAL_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				transitional: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						__( 'Lorem ipsum', 'amp' ),
					],
					recommended: MOST_RECOMMENDED,
				},
			};

		case ! hasThemeIssues && ! hasPluginIssues && userIsTechnical: // 7
			return {
				reader: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						READER_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				standard: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						__( 'Lorem ipsum', 'amp ' ),
					],
					recommended: MOST_RECOMMENDED,
				},
				transitional: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						TRANSITIONAL_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
			};

		case ! hasThemeIssues && ! hasPluginIssues && ! userIsTechnical: // 8
			return {
				reader: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						READER_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				standard: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						STANDARD_NONTECHNICAL_DO_NOT_USE,
					],
					recommended: LEAST_RECOMMENDED,
				},
				transitional: {
					compatibility: __( 'Lorem ipsum', 'amp' ),
					details: [
						__( 'Lorem ipsum', 'amp ' ),
					],
					recommended: MOST_RECOMMENDED,
				},
			};

		default: {
			throw new Error( __( 'A template mode recommendation case was not accounted for.', 'amp' ) );
		}
	}
}
/* eslint-enable @wordpress/no-unused-vars-before-return */
/* eslint-enable complexity */
