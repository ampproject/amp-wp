/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * External dependencies
 */

import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { AMPNotice, NOTICE_TYPE_SUCCESS, NOTICE_TYPE_INFO, NOTICE_TYPE_ERROR, NOTICE_SIZE_LARGE } from '../../../components/amp-notice';
import { TemplateModeOption } from '../../../components/template-mode-option';
import { READER, STANDARD, TRANSITIONAL } from '../../../common/constants';
import { MOST_RECOMMENDED, RECOMMENDED, getRecommendationLevels, getAllSelectionText, TECHNICAL, NON_TECHNICAL } from './get-selection-details';

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

	const sectionText = useMemo(
		() => getAllSelectionText( recommendationLevels, userIsTechnical ? TECHNICAL : NON_TECHNICAL ),
		[ recommendationLevels, userIsTechnical ],
	);

	const getRecommendationLevelType = ( recommended ) => {
		switch ( recommended ) {
			case MOST_RECOMMENDED:
				return NOTICE_TYPE_SUCCESS;

			case RECOMMENDED:
				return NOTICE_TYPE_INFO;

			default:
				return NOTICE_TYPE_ERROR;
		}
	};

	return (
		<form>
			<TemplateModeOption
				details={ sectionText.standard.details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/standard/"
				initialOpen={ true }
				mode={ STANDARD }
				previouslySelected={ savedCurrentMode === STANDARD && technicalQuestionChanged && ! firstTimeInWizard }
			>
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ getRecommendationLevelType( recommendationLevels[ STANDARD ] ) }>
					{ sectionText.standard.compatibility }
				</AMPNotice>
			</TemplateModeOption>

			<TemplateModeOption
				details={ sectionText.transitional.details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/transitional/"
				initialOpen={ true }
				mode={ TRANSITIONAL }
				previouslySelected={ savedCurrentMode === TRANSITIONAL && technicalQuestionChanged && ! firstTimeInWizard }
			>
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ getRecommendationLevelType( recommendationLevels[ TRANSITIONAL ] ) }>
					{ sectionText.transitional.compatibility }
				</AMPNotice>
			</TemplateModeOption>

			<TemplateModeOption
				details={ sectionText.reader.details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/reader/"
				initialOpen={ true }
				mode={ READER }
				previouslySelected={ savedCurrentMode === READER && technicalQuestionChanged && ! firstTimeInWizard }
			>
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ getRecommendationLevelType( recommendationLevels[ READER ] ) }>
					{ sectionText.reader.compatibility }
				</AMPNotice>
			</TemplateModeOption>
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
