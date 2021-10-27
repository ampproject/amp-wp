/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useContext, useMemo } from '@wordpress/element';

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
import { User } from '../components/user-context-provider';
import { SiteScan as SiteScanContext } from '../components/site-scan-context-provider';
import { READER, STANDARD, TRANSITIONAL } from '../common/constants';
import { AMPDrawer } from '../components/amp-drawer';
import { ReaderThemes } from '../components/reader-themes-context-provider';
import { ReaderThemeCarousel } from '../components/reader-theme-carousel';
import { getTemplateModeRecommendation, NOT_RECOMMENDED, RECOMMENDED } from '../common/helpers/get-template-mode-recommendation';

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
			sandboxing_level: sandboxingLevel,
			theme_support: editedThemeSupport,
		},
		updateOptions,
	} = useContext( Options );
	const {
		currentTheme: {
			is_reader_theme: currentThemeIsAmongReaderThemes,
		},
		selectedTheme,
		templateModeWasOverridden,
	} = useContext( ReaderThemes );
	const {
		hasSiteScanResults,
		isFetchingScannableUrls,
		pluginsWithAmpIncompatibility,
		themesWithAmpIncompatibility,
	} = useContext( SiteScanContext );
	const { developerToolsOption } = useContext( User );

	const templateModeRecommendation = useMemo( () => getTemplateModeRecommendation(
		{
			currentThemeIsAmongReaderThemes,
			hasPluginsWithAmpIncompatibility: pluginsWithAmpIncompatibility?.length > 0,
			hasSiteScanResults,
			hasThemesWithAmpIncompatibility: themesWithAmpIncompatibility?.length > 0,
			userIsTechnical: developerToolsOption === true,
		},
	), [ currentThemeIsAmongReaderThemes, developerToolsOption, hasSiteScanResults, pluginsWithAmpIncompatibility?.length, themesWithAmpIncompatibility?.length ] );

	const getLabelForTemplateMode = useCallback( ( mode ) => {
		if ( isFetchingScannableUrls ) {
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
	}, [ isFetchingScannableUrls, templateModeRecommendation ] );

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
			<TemplateModeOption
				details={ isFetchingScannableUrls ? null : templateModeRecommendation[ STANDARD ].details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/standard/"
				initialOpen={ false }
				mode={ STANDARD }
				labelExtra={ getLabelForTemplateMode( STANDARD ) }
			>
				{
					sandboxingLevel && (
						<fieldset>
							<h4 className="title">
								{ __( 'Sandboxing Level (Experimental)', 'amp' ) }
							</h4>
							<p>
								{ __( 'Try out a more flexible AMP by generating pages that use AMP components without requiring AMP validity! By selecting a sandboxing level, you are indicating the minimum degree of sanitization. For example, if you selected level 1 but have a page without any POST form and no custom scripts, it will still be served as valid AMP, the same as if you had selected level 3.', 'amp' ) }
							</p>
							<ol>
								<li>
									<input
										type="radio"
										id="sandboxing-level-1"
										checked={ 1 === sandboxingLevel }
										onChange={ () => {
											updateOptions( { sandboxing_level: 1 } );
										} }
									/>
									<label htmlFor="sandboxing-level-1">
										<strong>
											{ __( 'Loose:', 'amp' ) }
										</strong>
										{ ' ' + __( 'Do not remove any AMP-invalid markup by default, including custom scripts. CSS tree-shaking is disabled.', 'amp' ) }
									</label>
								</li>
								<li>
									<input
										type="radio"
										id="sandboxing-level-2"
										checked={ 2 === sandboxingLevel }
										onChange={ () => {
											updateOptions( { sandboxing_level: 2 } );
										} }
									/>
									<label htmlFor="sandboxing-level-2">
										<strong>
											{ __( 'Moderate:', 'amp' ) }
										</strong>
										{ ' ' + __( 'Remove non-AMP markup, but allow POST forms. CSS tree shaking is enabled.', 'amp' ) }
									</label>
								</li>
								<li>
									<input
										type="radio"
										id="sandboxing-level-3"
										checked={ 3 === sandboxingLevel }
										onChange={ () => {
											updateOptions( { sandboxing_level: 3 } );
										} }
									/>
									<label htmlFor="sandboxing-level-3">
										<strong>
											{ __( 'Strict:', 'amp' ) }
										</strong>
										{ ' ' + __( 'Require valid AMP.', 'amp' ) }
									</label>
								</li>

							</ol>
						</fieldset>
					)
				}
			</TemplateModeOption>
			<TemplateModeOption
				details={ isFetchingScannableUrls ? null : templateModeRecommendation[ TRANSITIONAL ].details }
				detailsUrl="https://amp-wp.org/documentation/getting-started/transitional/"
				initialOpen={ false }
				mode={ TRANSITIONAL }
				labelExtra={ getLabelForTemplateMode( TRANSITIONAL ) }
			/>
			<TemplateModeOption
				details={ isFetchingScannableUrls ? null : templateModeRecommendation[ READER ].details }
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
