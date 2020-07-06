/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PanelBody, PanelRow } from '@wordpress/components';
import { useMemo } from '@wordpress/element';

/**
 * External dependencies
 */

import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { AMPNotice, NOTICE_TYPE_SUCCESS, NOTICE_TYPE_INFO, NOTICE_TYPE_WARNING, NOTICE_SIZE_LARGE } from '../../components/amp-notice';
import { TemplateModeOption } from '../../../components/template-mode-option';
import { MOST_RECOMMENDED, RECOMMENDED, getRecommendationLevels, getAllSelectionText, TECHNICAL, NON_TECHNICAL, STANDARD, TRANSITIONAL, READER } from './get-selection-details';

/**
 * The interface for the mode selection screen. Avoids using context for easier testing.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.currentThemeIsAmongReaderThemes Whether the currently active theme is in the list of reader themes.
 * @param {boolean} props.developerToolsOption Whether the user has enabled developer tools.
 * @param {boolean} props.firstTimeInWizard Whether the wizard is running for the first time.
 * @param {boolean} props.technicalQuestionChanged Whether the user changed their technical question from the previous option.
 * @param {Array} props.pluginIssues The plugin issues found in the site scan.
 * @param {string} props.savedCurrentMode The current selected mode saved in the database.
 * @param {Array} props.themeIssues The theme issues found in the site scan.
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
				return NOTICE_TYPE_WARNING;
		}
	};

	return (
		<form>
			<TemplateModeOption
				details={ sectionText.standard.details }
				mode="standard"
				previouslySelected={ savedCurrentMode === 'standard' && technicalQuestionChanged && ! firstTimeInWizard }
			>
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ getRecommendationLevelType( recommendationLevels[ STANDARD ] ) }>
					<PanelBody title={ sectionText.standard.compatibility } initialOpen={ false } opened={ false }>
						<PanelRow>
							<h3>
								{ __( 'Compatibility', 'amp' ) }
							</h3>
							<p>
								{ 'Lorem ipsum dolar sit amet. ' }
								{ /* @todo Temporary URL. */ }
								<a href="http://amp-wp.org" target="_blank" rel="noreferrer">
									{ __( 'Learn more.', 'amp' ) }
								</a>
							</p>
						</PanelRow>
					</PanelBody>
				</AMPNotice>
			</TemplateModeOption>

			<TemplateModeOption
				details={ sectionText.transitional.details }
				mode="transitonal"
				previouslySelected={ savedCurrentMode === 'transitional' && technicalQuestionChanged && ! firstTimeInWizard }
			>
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ getRecommendationLevelType( recommendationLevels[ TRANSITIONAL ] ) }>
					<PanelBody title={ sectionText.transitional.compatibility } initialOpen={ false } opened={ false }>
						<PanelRow>
							<h3>
								{ __( 'Compatibility', 'amp' ) }
							</h3>
							<p>
								{ 'Lorem ipsum dolar sit amet. ' }
								{ /* @todo Temporary URL. */ }
								<a href="http://amp-wp.org" target="_blank" rel="noreferrer">
									{ __( 'Learn more.', 'amp' ) }
								</a>
							</p>
						</PanelRow>
					</PanelBody>
				</AMPNotice>
			</TemplateModeOption>

			<TemplateModeOption
				details={ sectionText.reader.details }
				mode="reader"
				previouslySelected={ savedCurrentMode === 'reader' && technicalQuestionChanged && ! firstTimeInWizard }
			>
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ getRecommendationLevelType( recommendationLevels[ READER ] ) }>
					<PanelBody title={ sectionText.reader.compatibility } initialOpen={ false } opened={ false }>
						<PanelRow>
							<h3>
								{ __( 'Compatibility', 'amp' ) }
							</h3>
							<p>
								{ 'Lorem ipsum dolar sit amet. ' }
								{ /* @todo Temporary URL. */ }
								<a href="http://amp-wp.org" target="_blank" rel="noreferrer">
									{ __( 'Learn more.', 'amp' ) }
								</a>
							</p>
						</PanelRow>
					</PanelBody>
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
