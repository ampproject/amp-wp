/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */

import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import {
	AMPNotice,
	NOTICE_TYPE_SUCCESS,
	NOTICE_SIZE_SMALL,
} from '../../../components/amp-notice';
import { TemplateModeOption } from '../../../components/template-mode-option';
import { READER, STANDARD, TRANSITIONAL } from '../../../common/constants';
import {
	RECOMMENDED,
	NOT_RECOMMENDED,
	getRecommendationLevels,
} from './get-selection-details';

/**
 * Small notice indicating a mode is recommended.
 */
function RecommendedNotice() {
	return (
		<AMPNotice size={ NOTICE_SIZE_SMALL } type={ NOTICE_TYPE_SUCCESS }>
			{ __( 'Recommended', 'amp' ) }
		</AMPNotice>
	);
}

/**
 * Determine if a template mode option should be initially open.
 *
 * @param {string} mode                 Template mode to check.
 * @param {Array}  recommendationLevels Recommendation levels.
 * @param {string} savedCurrentMode     Currently saved template mode.
 */
function isInitiallyOpen( mode, recommendationLevels, savedCurrentMode ) {
	if ( savedCurrentMode === mode ) {
		return true;
	}

	switch ( recommendationLevels[ mode ].level ) {
		case RECOMMENDED:
			return true;

		case NOT_RECOMMENDED:
			return false;

		/**
		 * For NEUTRAL, the option should be initially open if no other mode is
		 * RECOMMENDED.
		 */
		default:
			return ! Boolean( Object.values( recommendationLevels ).find( ( item ) => item.level === RECOMMENDED ) );
	}
}

/**
 * The interface for the mode selection screen. Avoids using context for easier testing.
 *
 * @param {Object}  props                                 Component props.
 * @param {boolean} props.currentThemeIsAmongReaderThemes Whether the currently active theme is in the list of reader themes.
 * @param {boolean} props.developerToolsOption            Whether the user has enabled developer tools.
 * @param {boolean} props.firstTimeInWizard               Whether the wizard is running for the first time.
 * @param {boolean} props.technicalQuestionChanged        Whether the user changed their technical question from the previous option.
 * @param {Array}   props.pluginIssues                    The plugin issues found in the site scan.
 * @param {string}  props.savedCurrentMode                The current selected mode saved in the database.
 * @param {Array}   props.themeIssues                     The theme issues found in the site scan.
 */
export function ScreenUI( { currentThemeIsAmongReaderThemes, developerToolsOption, firstTimeInWizard, technicalQuestionChanged, pluginIssues, savedCurrentMode, themeIssues } ) {
	const userIsTechnical = useMemo( () => developerToolsOption === true, [ developerToolsOption ] );

	const recommendationLevels = useMemo( () => getRecommendationLevels(
		{
			currentThemeIsAmongReaderThemes,
			userIsTechnical,
			hasScanResults: null !== pluginIssues && null !== themeIssues,
			hasPluginIssues: pluginIssues && 0 < pluginIssues.length,
			hasThemeIssues: themeIssues && 0 < themeIssues.length,
		},
	), [ currentThemeIsAmongReaderThemes, themeIssues, pluginIssues, userIsTechnical ] );

	return (
		<form>
			<TemplateModeOption
				details={ recommendationLevels[ READER ].details }
				initialOpen={ isInitiallyOpen( READER, recommendationLevels, savedCurrentMode ) }
				mode={ READER }
				previouslySelected={ savedCurrentMode === READER && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ recommendationLevels[ READER ].level === RECOMMENDED ? <RecommendedNotice /> : null }
			/>

			<TemplateModeOption
				details={ recommendationLevels[ TRANSITIONAL ].details }
				initialOpen={ isInitiallyOpen( TRANSITIONAL, recommendationLevels, savedCurrentMode ) }
				mode={ TRANSITIONAL }
				previouslySelected={ savedCurrentMode === TRANSITIONAL && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ recommendationLevels[ TRANSITIONAL ].level === RECOMMENDED ? <RecommendedNotice /> : null }
			/>

			<TemplateModeOption
				details={ recommendationLevels[ STANDARD ].details }
				initialOpen={ isInitiallyOpen( STANDARD, recommendationLevels, savedCurrentMode ) }
				mode={ STANDARD }
				previouslySelected={ savedCurrentMode === STANDARD && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ recommendationLevels[ STANDARD ].level === RECOMMENDED ? <RecommendedNotice /> : null }
			/>
		</form>
	);
}

ScreenUI.propTypes = {
	currentThemeIsAmongReaderThemes: PropTypes.bool.isRequired,
	developerToolsOption: PropTypes.bool,
	firstTimeInWizard: PropTypes.bool,
	technicalQuestionChanged: PropTypes.bool,
	pluginIssues: PropTypes.arrayOf( PropTypes.string ),
	savedCurrentMode: PropTypes.string,
	themeIssues: PropTypes.arrayOf( PropTypes.string ),
};
