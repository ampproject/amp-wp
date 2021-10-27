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
	getTemplateModeRecommendation,
} from '../../../common/helpers/get-template-mode-recommendation';

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
 * @param {string} mode             Template mode to check.
 * @param {Array}  selectionDetails Selection details.
 * @param {string} savedCurrentMode Currently saved template mode.
 */
function isInitiallyOpen( mode, selectionDetails, savedCurrentMode ) {
	if ( savedCurrentMode === mode ) {
		return true;
	}

	switch ( selectionDetails[ mode ].recommendationLevel ) {
		case RECOMMENDED:
			return true;

		case NOT_RECOMMENDED:
			return false;

		/**
		 * For NEUTRAL, the option should be initially open if no other mode is
		 * RECOMMENDED.
		 */
		default:
			return ! Boolean( Object.values( selectionDetails ).find( ( item ) => item.recommendationLevel === RECOMMENDED ) );
	}
}

/**
 * The interface for the mode selection screen. Avoids using context for easier testing.
 *
 * @param {Object}   props                                 Component props.
 * @param {boolean}  props.currentThemeIsAmongReaderThemes Whether the currently active theme is in the list of reader themes.
 * @param {boolean}  props.developerToolsOption            Whether the user has enabled developer tools.
 * @param {boolean}  props.firstTimeInWizard               Whether the wizard is running for the first time.
 * @param {boolean}  props.hasSiteScanResults              Whether there are available site scan results.
 * @param {boolean}  props.technicalQuestionChanged        Whether the user changed their technical question from the previous option.
 * @param {string[]} props.pluginsWithAmpIncompatibility   A list of plugin slugs causing AMP incompatibility.
 * @param {string}   props.savedCurrentMode                The current selected mode saved in the database.
 * @param {string[]} props.themesWithAmpIncompatibility    A list of theme slugs causing AMP incompatibility.
 */
export function ScreenUI( {
	currentThemeIsAmongReaderThemes,
	developerToolsOption,
	firstTimeInWizard,
	hasSiteScanResults,
	pluginsWithAmpIncompatibility,
	savedCurrentMode,
	technicalQuestionChanged,
	themesWithAmpIncompatibility,
} ) {
	const templateModeRecommendation = useMemo( () => getTemplateModeRecommendation(
		{
			currentThemeIsAmongReaderThemes,
			hasPluginsWithAmpIncompatibility: pluginsWithAmpIncompatibility?.length > 0,
			hasSiteScanResults,
			hasThemesWithAmpIncompatibility: themesWithAmpIncompatibility?.length > 0,
			userIsTechnical: developerToolsOption === true,
		},
	), [ currentThemeIsAmongReaderThemes, developerToolsOption, hasSiteScanResults, pluginsWithAmpIncompatibility, themesWithAmpIncompatibility ] );

	return (
		<form>
			<TemplateModeOption
				details={ templateModeRecommendation[ READER ].details }
				initialOpen={ isInitiallyOpen( READER, templateModeRecommendation, savedCurrentMode ) }
				mode={ READER }
				previouslySelected={ savedCurrentMode === READER && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ templateModeRecommendation[ READER ].recommendationLevel === RECOMMENDED ? <RecommendedNotice /> : null }
			/>

			<TemplateModeOption
				details={ templateModeRecommendation[ TRANSITIONAL ].details }
				initialOpen={ isInitiallyOpen( TRANSITIONAL, templateModeRecommendation, savedCurrentMode ) }
				mode={ TRANSITIONAL }
				previouslySelected={ savedCurrentMode === TRANSITIONAL && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ templateModeRecommendation[ TRANSITIONAL ].recommendationLevel === RECOMMENDED ? <RecommendedNotice /> : null }
			/>

			<TemplateModeOption
				details={ templateModeRecommendation[ STANDARD ].details }
				initialOpen={ isInitiallyOpen( STANDARD, templateModeRecommendation, savedCurrentMode ) }
				mode={ STANDARD }
				previouslySelected={ savedCurrentMode === STANDARD && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ templateModeRecommendation[ STANDARD ].recommendationLevel === RECOMMENDED ? <RecommendedNotice /> : null }
			/>
		</form>
	);
}

ScreenUI.propTypes = {
	currentThemeIsAmongReaderThemes: PropTypes.bool.isRequired,
	developerToolsOption: PropTypes.bool,
	firstTimeInWizard: PropTypes.bool,
	hasSiteScanResults: PropTypes.bool,
	technicalQuestionChanged: PropTypes.bool,
	pluginsWithAmpIncompatibility: PropTypes.arrayOf( PropTypes.string ),
	savedCurrentMode: PropTypes.string,
	themesWithAmpIncompatibility: PropTypes.arrayOf( PropTypes.string ),
};
