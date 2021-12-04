/**
 * WordPress dependencies
 */
import { useEffect, useContext } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';
import { useTemplateModeRecommendation } from '../../../components/use-template-mode-recommendation';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../../components/options-context-provider';
import { TemplateModeOverride } from '../../components/template-mode-override-context-provider';
import { ScreenUI } from './screen-ui';

/**
 * Screen for selecting the template mode.
 */
export function TemplateMode() {
	const { setCanGoForward } = useContext( Navigation );
	const { editedOptions: { theme_support: themeSupport }, originalOptions } = useContext( Options );
	const { technicalQuestionChangedAtLeastOnce } = useContext( TemplateModeOverride );
	const templateModeRecommendation = useTemplateModeRecommendation();

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( undefined !== themeSupport ) {
			setCanGoForward( true );
		}
	}, [ setCanGoForward, themeSupport ] );

	// The actual display component should avoid using global context directly. This will facilitate developing and testing the UI using different options.
	return (
		<div className="template-modes">
			<div className="template-modes__header">
				<h1>
					{ __( 'Template Modes', 'amp' ) }
				</h1>
				{ /* dangerouslySetInnerHTML reason: Injection of links. */ }
				<p dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholders are links to amp-wp.org website. */
						__( 'Based on site scan results the AMP plugin provides the following choices. Learn more about the <a href="%1$s" target="_blank" rel="noreferrer noopener">AMP experience with different modes</a> and availability of <a href="%2$s" target="_blank" rel="noreferrer noopener">AMP components in the ecosystem</a>.', 'amp' ),
						'https://amp-wp.org/documentation/getting-started/template-modes/',
						'https://amp-wp.org/ecosystem/',
					),
				} } />
			</div>
			<ScreenUI
				currentMode={ themeSupport }
				firstTimeInWizard={ false === originalOptions.plugin_configured }
				savedCurrentMode={ originalOptions.theme_support }
				technicalQuestionChanged={ technicalQuestionChangedAtLeastOnce }
				templateModeRecommendation={ templateModeRecommendation }
			/>
		</div>
	);
}
