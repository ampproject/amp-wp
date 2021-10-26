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
	getSelectionDetails,
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
 * @param {boolean}  props.technicalQuestionChanged        Whether the user changed their technical question from the previous option.
 * @param {string[]} props.pluginsWithAMPIncompatibility   A list of plugin slugs causing AMP incompatibility.
 * @param {string}   props.savedCurrentMode                The current selected mode saved in the database.
 * @param {string[]} props.themesWithAMPIncompatibility    A list of theme slugs causing AMP incompatibility.
 */
export function ScreenUI( {
	currentThemeIsAmongReaderThemes,
	developerToolsOption,
	firstTimeInWizard,
	technicalQuestionChanged,
	pluginsWithAMPIncompatibility,
	savedCurrentMode,
	themesWithAMPIncompatibility,
} ) {
	const userIsTechnical = useMemo( () => developerToolsOption === true, [ developerToolsOption ] );

	const selectionDetails = useMemo( () => getSelectionDetails(
		{
			currentThemeIsAmongReaderThemes,
			userIsTechnical,
			hasScanResults: null !== pluginsWithAMPIncompatibility && null !== themesWithAMPIncompatibility,
			hasPluginsWithAMPIncompatibility: pluginsWithAMPIncompatibility && 0 < pluginsWithAMPIncompatibility.length,
			hasThemesWithAMPIncompatibility: themesWithAMPIncompatibility && 0 < themesWithAMPIncompatibility.length,
		},
	), [ currentThemeIsAmongReaderThemes, themesWithAMPIncompatibility, pluginsWithAMPIncompatibility, userIsTechnical ] );

	return (
		<form>
			<TemplateModeOption
				details={ selectionDetails[ READER ].details }
				initialOpen={ isInitiallyOpen( READER, selectionDetails, savedCurrentMode ) }
				mode={ READER }
				previouslySelected={ savedCurrentMode === READER && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ selectionDetails[ READER ].recommendationLevel === RECOMMENDED ? <RecommendedNotice /> : null }
			/>

			<TemplateModeOption
				details={ selectionDetails[ TRANSITIONAL ].details }
				initialOpen={ isInitiallyOpen( TRANSITIONAL, selectionDetails, savedCurrentMode ) }
				mode={ TRANSITIONAL }
				previouslySelected={ savedCurrentMode === TRANSITIONAL && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ selectionDetails[ TRANSITIONAL ].recommendationLevel === RECOMMENDED ? <RecommendedNotice /> : null }
			/>

			<TemplateModeOption
				details={ selectionDetails[ STANDARD ].details }
				initialOpen={ isInitiallyOpen( STANDARD, selectionDetails, savedCurrentMode ) }
				mode={ STANDARD }
				previouslySelected={ savedCurrentMode === STANDARD && technicalQuestionChanged && ! firstTimeInWizard }
				labelExtra={ selectionDetails[ STANDARD ].recommendationLevel === RECOMMENDED ? <RecommendedNotice /> : null }
			/>
		</form>
	);
}

ScreenUI.propTypes = {
	currentThemeIsAmongReaderThemes: PropTypes.bool.isRequired,
	developerToolsOption: PropTypes.bool,
	firstTimeInWizard: PropTypes.bool,
	technicalQuestionChanged: PropTypes.bool,
	pluginsWithAMPIncompatibility: PropTypes.arrayOf( PropTypes.string ),
	savedCurrentMode: PropTypes.string,
	themesWithAMPIncompatibility: PropTypes.arrayOf( PropTypes.string ),
};
