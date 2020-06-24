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
import './style.css';
import { Selectable } from '../../components/selectable';
import { AMPNotice, NOTICE_TYPE_SUCCESS, NOTICE_TYPE_INFO, NOTICE_TYPE_WARNING, NOTICE_SIZE_LARGE } from '../../components/amp-notice';
import { Standard } from '../../components/svg/standard';
import { Transitional } from '../../components/svg/transitional';
import { Reader } from '../../components/svg/reader';
import { MOST_RECOMMENDED, RECOMMENDED, getRecommendationLevels, getAllSelectionText, TECHNICAL, NON_TECHNICAL, STANDARD, TRANSITIONAL, READER, NOT_RECOMMENDED } from './get-selection-details';

/**
 * An individual mode selection component.
 *
 * @param {Object} props Component props.
 * @param {string|Object} props.compatibility Compatibility content.
 * @param {string} props.id A string for the input's HTML ID.
 * @param {string|Object} props.illustration An illustration for the selection.
 * @param {boolean} props.isCurrentSavedSelection Whether the mode is currently saved as the selected mode.
 * @param {Array} props.details Array of strings representing details about the mode and recommendation.
 * @param {Function} props.onChange Callback to select the mode.
 * @param {number} props.recommended Recommendation level. -1: not recommended. 0: good. 1: Most recommended.
 * @param {boolean} props.selected Whether the mode is selected.
 * @param {string} props.title The title for the selection.
 */
export function Selection( { compatibility, id, illustration, isCurrentSavedSelection, details, onChange, recommended, selected, title } ) {
	const recommendationLevelType = useMemo( () => {
		switch ( recommended ) {
			case MOST_RECOMMENDED:
				return NOTICE_TYPE_SUCCESS;

			case RECOMMENDED:
				return NOTICE_TYPE_INFO;

			default:
				return NOTICE_TYPE_WARNING;
		}
	}, [ recommended ] );

	return (
		<Selectable className="template-mode-selection" selected={ selected }>
			<label htmlFor={ id }>
				<div className="template-mode-selection__input-container">
					<input
						type="radio"
						id={ id }
						checked={ selected }
						onChange={ onChange }
					/>
				</div>
				<div className="template-mode-selection__illustration">
					{ illustration }
				</div>
				<div className="template-mode-selection__description">
					<h2>
						{ title }
					</h2>
					{ isCurrentSavedSelection && (
						<div className="template-mode-selection__current-selection">
							<svg width="15" height="21" viewBox="0 0 15 21" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9.60669 2.83203V7.83203C9.60669 8.95203 9.97669 9.99203 10.6067 10.832H4.60669C5.25669 9.97203 5.60669 8.93203 5.60669 7.83203V2.83203H9.60669ZM12.6067 0.832031H2.60669C2.05669 0.832031 1.60669 1.28203 1.60669 1.83203C1.60669 2.38203 2.05669 2.83203 2.60669 2.83203H3.60669V7.83203C3.60669 9.49203 2.26669 10.832 0.606689 10.832V12.832H6.57669V19.832L7.57669 20.832L8.57669 19.832V12.832H14.6067V10.832C12.9467 10.832 11.6067 9.49203 11.6067 7.83203V2.83203H12.6067C13.1567 2.83203 13.6067 2.38203 13.6067 1.83203C13.6067 1.28203 13.1567 0.832031 12.6067 0.832031Z" fill="#333D47" />
							</svg>
							{ __( 'Previously selected', 'amp' ) }
						</div>
					) }
				</div>
			</label>
			<div
				className="template-mode-selection__details"
			>
				<p>
					<span dangerouslySetInnerHTML={ { __html: details } } />
					{ ' ' }
					{ /* @todo Temporary URL. */ }
					<a href="http://amp-wp.org" target="_blank" rel="noreferrer">
						{ __( 'Learn more.', 'amp' ) }
					</a>
				</p>
			</div>
			<AMPNotice size={ NOTICE_SIZE_LARGE } type={ recommendationLevelType }>
				<PanelBody title={ compatibility } initialOpen={ false } opened={ false }>
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
		</Selectable>
	);
}

Selection.propTypes = {
	compatibility: PropTypes.node.isRequired,
	details: PropTypes.node.isRequired,
	id: PropTypes.string.isRequired,
	illustration: PropTypes.node.isRequired,
	isCurrentSavedSelection: PropTypes.bool.isRequired,
	onChange: PropTypes.func.isRequired,
	recommended: PropTypes.oneOf( [ RECOMMENDED, NOT_RECOMMENDED, MOST_RECOMMENDED ] ).isRequired,
	selected: PropTypes.bool.isRequired,
	title: PropTypes.string.isRequired,
};

/**
 * The interface for the mode selection screen. Avoids using context for easier testing.
 *
 * @param {Object} props Component props.
 * @param {string} props.currentMode The selected mode.
 * @param {boolean} props.developerToolsOption Whether the user has enabled developer tools.
 * @param {Array} props.pluginIssues The plugin issues found in the site scan.
 * @param {string} props.savedCurrentMode The current selected mode saved in the database.
 * @param {Function} props.setCurrentMode The callback to update the selected mode.
 * @param {Array} props.themeIssues The theme issues found in the site scan.
 * @param props.savedCurrentMode
 */
export function ScreenUI( { currentMode, developerToolsOption, pluginIssues, savedCurrentMode, setCurrentMode, themeIssues } ) {
	const standardId = 'standard-mode';
	const transitionalId = 'transitional-mode';
	const readerId = 'reader-mode';

	const userIsTechnical = useMemo( () => developerToolsOption === true, [ developerToolsOption ] );

	const recommendationLevels = useMemo( () => getRecommendationLevels(
		{
			userIsTechnical,
			hasScanResults: null !== pluginIssues && null !== themeIssues,
			hasPluginIssues: pluginIssues && 0 < pluginIssues.length,
			hasThemeIssues: themeIssues && 0 < themeIssues.length,
		},
	), [ themeIssues, pluginIssues, userIsTechnical ] );

	const sectionText = useMemo(
		() => getAllSelectionText( recommendationLevels, userIsTechnical ? TECHNICAL : NON_TECHNICAL ),
		[ recommendationLevels, userIsTechnical ],
	);

	return (
		<form>
			<Selection
				compatibility={ sectionText.standard.compatibility }
				details={ sectionText.standard.details }
				id={ standardId }
				illustration={ <Standard /> }
				isCurrentSavedSelection={ savedCurrentMode === 'standard' }
				onChange={ () => {
					setCurrentMode( 'standard' );
				} }
				recommended={ recommendationLevels[ STANDARD ] }
				selected={ currentMode === 'standard' }
				title={ __( 'Standard', 'amp' ) }
			/>

			<Selection
				compatibility={ sectionText.transitional.compatibility }
				details={ sectionText.transitional.details }
				id={ transitionalId }
				illustration={ <Transitional /> }
				isCurrentSavedSelection={ savedCurrentMode === 'transitional' }
				onChange={ () => {
					setCurrentMode( 'transitional' );
				} }
				recommended={ recommendationLevels[ TRANSITIONAL ] }
				selected={ currentMode === 'transitional' }
				title={ __( 'Transitional', 'amp' ) }
			/>

			<Selection
				compatibility={ sectionText.reader.compatibility }
				details={ sectionText.reader.details }
				id={ readerId }
				illustration={ <Reader /> }
				isCurrentSavedSelection={ savedCurrentMode === 'reader' }
				onChange={ () => {
					setCurrentMode( 'reader' );
				} }
				recommended={ recommendationLevels[ READER ] }
				selected={ currentMode === 'reader' }
				title={ __( 'Reader', 'amp' ) }
			/>
		</form>
	);
}

ScreenUI.propTypes = {
	currentMode: PropTypes.string,
	developerToolsOption: PropTypes.bool,
	setCurrentMode: PropTypes.func.isRequired,
	pluginIssues: PropTypes.arrayOf( PropTypes.string ),
	savedCurrentMode: PropTypes.string,
	themeIssues: PropTypes.arrayOf( PropTypes.string ),
};
