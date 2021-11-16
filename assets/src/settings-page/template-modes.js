/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { TemplateModeOption } from '../components/template-mode-option';
import {
	AMPNotice,
	NOTICE_SIZE_LARGE,
	NOTICE_TYPE_INFO,
	NOTICE_SIZE_SMALL,
	NOTICE_TYPE_WARNING,
} from '../components/amp-notice';
import { Options } from '../components/options-context-provider';
import { READER, STANDARD, TRANSITIONAL } from '../common/constants';
import { AMPDrawer } from '../components/amp-drawer';
import { ReaderThemes } from '../components/reader-themes-context-provider';
import { ReaderThemeCarousel } from '../components/reader-theme-carousel';
import {
	NOT_RECOMMENDED,
	RECOMMENDED,
	useTemplateModeRecommendation,
} from '../components/use-template-mode-recommendation';

/**
 * Small notice indicating a mode is recommended.
 */
function RecommendedNotice() {
	return (
		<AMPNotice size={ NOTICE_SIZE_SMALL }>
			{ __( 'Recommended', 'amp' ) }
		</AMPNotice>
	);
}

/**
 * Small notice indicating a mode is not recommended.
 */
function NotRecommendedNotice() {
	return (
		<AMPNotice size={ NOTICE_SIZE_SMALL } type={ NOTICE_TYPE_WARNING }>
			{ __( 'Not recommended', 'amp' ) }
		</AMPNotice>
	);
}

/**
 * Template modes section of the settings page.
 *
 * @param {Object}  props                   Component props.
 * @param {boolean} props.focusReaderThemes Whether the reader themes drawer should be opened and focused.
 */
export function TemplateModes( { focusReaderThemes } ) {
	const {
		editedOptions: {
			theme_support: editedThemeSupport,
		},
	} = useContext( Options );
	const { selectedTheme, templateModeWasOverridden } = useContext( ReaderThemes );
	const { templateModeRecommendation, staleTemplateModeRecommendation } = useTemplateModeRecommendation();

	const getLabelForTemplateMode = useCallback( ( mode ) => {
		if ( ! templateModeRecommendation ) {
			return null;
		}

		switch ( templateModeRecommendation[ mode ].recommendationLevel ) {
			case RECOMMENDED:
				return <RecommendedNotice />;
			case NOT_RECOMMENDED:
				return <NotRecommendedNotice />;
			default:
				return null;
		}
	}, [ templateModeRecommendation ] );

	return (
		<section className="template-modes" id="template-modes">
			<h2>
				{ __( 'Template Mode', 'amp' ) }
			</h2>
			{ templateModeWasOverridden && (
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
					{ __( 'Because you selected a Reader theme that is the same as your site\'s active theme, your site has automatically been switched to Transitional template mode.', 'amp' ) }
				</AMPNotice>
			) }
			{ staleTemplateModeRecommendation && (
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
					{ __( 'Because the Site Scan results are stale, the Template Mode recommendation may not be accurate. Rescan your site to ensure the recommendation is up to date.', 'amp' ) }
				</AMPNotice>
			) }
			<TemplateModeOption
				details={ templateModeRecommendation?.[ STANDARD ]?.details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/standard/"
				initialOpen={ false }
				mode={ STANDARD }
				labelExtra={ getLabelForTemplateMode( STANDARD ) }
			/>
			<TemplateModeOption
				details={ templateModeRecommendation?.[ TRANSITIONAL ]?.details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/transitional/"
				initialOpen={ false }
				mode={ TRANSITIONAL }
				labelExtra={ getLabelForTemplateMode( TRANSITIONAL ) }
			/>
			<TemplateModeOption
				details={ templateModeRecommendation?.[ READER ]?.details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/reader/"
				initialOpen={ false }
				mode={ READER }
				labelExtra={ getLabelForTemplateMode( READER ) }
			/>
			{ READER === editedThemeSupport && (
				<AMPDrawer
					selected={ true }
					heading={ (
						<div className="reader-themes__heading">
							<h3>
								{ sprintf(
									/* translators: placeholder is a theme name. */
									__( 'Reader theme: %s', 'amp' ),
									selectedTheme.name || '',
								) }
							</h3>
						</div>
					) }
					hiddenTitle={ __( 'Show reader themes', 'amp' ) }
					id="reader-themes"
					initialOpen={ focusReaderThemes }
				>
					<ReaderThemeCarousel />
				</AMPDrawer>
			) }
		</section>
	);
}
TemplateModes.propTypes = {
	focusReaderThemes: PropTypes.bool.isRequired,
};
