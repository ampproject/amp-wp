/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

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
} from '../../../components/use-template-mode-recommendation';

/**
 * Small notice indicating a mode is recommended.
 */
function RecommendedNotice() {
	return (
		<AMPNotice size={NOTICE_SIZE_SMALL} type={NOTICE_TYPE_SUCCESS}>
			{__('Recommended', 'amp')}
		</AMPNotice>
	);
}

/**
 * Determine if a template mode option should be initially open.
 *
 * @param {string} mode             Template mode to check.
 * @param {Object} selectionDetails Selection details.
 * @param {string} savedCurrentMode Currently saved template mode.
 */
function isInitiallyOpen(mode, selectionDetails, savedCurrentMode) {
	if (savedCurrentMode === mode || !selectionDetails) {
		return true;
	}

	switch (selectionDetails[mode].recommendationLevel) {
		case RECOMMENDED:
			return true;

		case NOT_RECOMMENDED:
			return false;

		/**
		 * For NEUTRAL, the option should be initially open if no other mode is
		 * RECOMMENDED.
		 */
		default:
			return !Object.values(selectionDetails).some(
				(item) => item.recommendationLevel === RECOMMENDED
			);
	}
}

/**
 * Create a template mode option with the appropriate props.
 *
 * @param {string}  mode                       The template mode.
 * @param {Object}  templateModeRecommendation Recommendations for each template mode.
 * @param {string}  savedCurrentMode           The current selected mode saved in the database.
 * @param {boolean} technicalQuestionChanged   Whether the user changed their technical question from the previous option.
 * @param {boolean} firstTimeInWizard          Whether the wizard is running for the first time.
 * @return {Object} TemplateModeOption component.
 */
function createTemplateModeOption(
	mode,
	templateModeRecommendation,
	savedCurrentMode,
	technicalQuestionChanged,
	firstTimeInWizard
) {
	const modeRecommendation = templateModeRecommendation?.[mode];

	return (
		<TemplateModeOption
			key={mode}
			details={modeRecommendation?.details}
			initialOpen={isInitiallyOpen(
				mode,
				templateModeRecommendation,
				savedCurrentMode
			)}
			mode={mode}
			previouslySelected={
				savedCurrentMode === mode &&
				technicalQuestionChanged &&
				!firstTimeInWizard
			}
			labelExtra={
				modeRecommendation?.recommendationLevel === RECOMMENDED ? (
					<RecommendedNotice />
				) : null
			}
		/>
	);
}

/**
 * The interface for the mode selection screen. Avoids using context for easier testing.
 *
 * @param {Object}  props                            Component props.
 * @param {boolean} props.firstTimeInWizard          Whether the wizard is running for the first time.
 * @param {boolean} props.technicalQuestionChanged   Whether the user changed their technical question from the previous option.
 * @param {Object}  props.templateModeRecommendation Recommendations for each template mode.
 * @param {string}  props.savedCurrentMode           The current selected mode saved in the database.
 */
export function ScreenUI({
	firstTimeInWizard,
	savedCurrentMode,
	technicalQuestionChanged,
	templateModeRecommendation,
}) {
	const modes = [READER, TRANSITIONAL, STANDARD];

	return (
		<form>
			{modes.map((mode) =>
				createTemplateModeOption(
					mode,
					templateModeRecommendation,
					savedCurrentMode,
					technicalQuestionChanged,
					firstTimeInWizard
				)
			)}
		</form>
	);
}

ScreenUI.propTypes = {
	firstTimeInWizard: PropTypes.bool,
	savedCurrentMode: PropTypes.string,
	technicalQuestionChanged: PropTypes.bool,
	templateModeRecommendation: PropTypes.shape({
		[READER]: PropTypes.shape({
			recommendationLevel: PropTypes.string,
			details: PropTypes.array,
		}),
		[TRANSITIONAL]: PropTypes.shape({
			recommendationLevel: PropTypes.string,
			details: PropTypes.array,
		}),
		[STANDARD]: PropTypes.shape({
			recommendationLevel: PropTypes.string,
			details: PropTypes.array,
		}),
	}),
};
