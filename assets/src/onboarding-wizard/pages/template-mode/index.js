/**
 * WordPress dependencies
 */
import { useEffect, useContext, createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

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
				<p>
					{
						createInterpolateElement(
							__( 'Based on site scan results the AMP plugin provides the following choices. Learn more about the <GettingStartedUrl>AMP experience with different modes</GettingStartedUrl> and availability of <EcosystemUrl>AMP components in the ecosystem</EcosystemUrl>.', 'amp' ),
							{
								/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
								GettingStartedUrl: <a href="https://amp-wp.org/documentation/getting-started/template-modes/" target="_blank" rel="noreferrer noopener" />,
								EcosystemUrl: <a href="https://amp-wp.org/ecosystem/" target="_blank" rel="noreferrer noopener" />,
								/* eslint-enable jsx-a11y/anchor-has-content */
							},
						)
					}
				</p>
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
