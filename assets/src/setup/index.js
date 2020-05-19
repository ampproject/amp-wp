/**
 * WordPress dependencies
 */
import { render, useState, useMemo } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { APP_ROOT_ID } from 'amp-setup'; // WP localized data.

/**
 * Internal dependencies
 */
import './setup.css';
import { TechnicalBackground } from './pages/technical-background';
import { Stepper } from './components/stepper';
import { Goals } from './pages/goals';
import { TemplateMode } from './pages/template-mode';
import { ChooseReaderTheme } from './pages/choose-reader-theme';
import { SiteConfigurationSummary } from './pages/site-configuration-summary';
import { Save } from './pages/save';

const PAGES = [
	{
		navTitle: __( 'Technical Background', 'amp' ),
		PageComponent: TechnicalBackground,
	},
	{
		navTitle: __( 'Site Goals', 'amp' ),
		PageComponent: Goals,
	},
	{
		navTitle: __( 'Template Mode', 'amp' ),
		PageComponent: TemplateMode,
	},
	{
		navTitle: __( 'Choose Reader Theme', 'amp' ),
		PageComponent: ChooseReaderTheme,
	},
	{
		navTitle: __( 'Site Configuration Summary', 'amp' ),
		PageComponent: SiteConfigurationSummary,
	},
	{
		navTitle: __( 'Save', 'amp' ),
		PageComponent: Save,
	},
];

function getPage( index ) {
	return PAGES[ index ];
}

function Setup() {
	const [ activePageIndex, setActivePageIndex ] = useState( 0 );

	const { PageComponent } = useMemo( () => getPage( activePageIndex ), [ activePageIndex ] );

	return (
		<div className="amp-setup-container">
			<div className="amp-setup">
				<Stepper
					activePageIndex={ activePageIndex }
					pages={ PAGES }
				/>
				<div className="page">
					<PageComponent />

					<div>
						{ 0 === activePageIndex
							? <span />
							: (
								<button onClick={ () => {
									setActivePageIndex( activePageIndex - 1 );
								} }>
									{ __( 'Back', 'amp' ) }
								</button>
							)
						}
						{ PAGES.length - 1 === activePageIndex
							? <span />
							: (
								<button onClick={ () => {
									setActivePageIndex( activePageIndex + 1 );
								} }>
									{ __( 'Next', 'amp' ) }
								</button>
							)
						}
					</div>

				</div>
			</div>
		</div>
	);
}

domReady( () => {
	const root = document.getElementById( APP_ROOT_ID );

	if ( root ) {
		render( <Setup />, root );
	}
} );
