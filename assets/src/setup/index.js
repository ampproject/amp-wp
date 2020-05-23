/**
 * WordPress dependencies
 */
import { render, useState, useMemo, useCallback } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';
import { Button, Panel } from '@wordpress/components';
import '@wordpress/components/build-style/style.css';

/**
 * External dependencies
 */
import { APP_ROOT_ID, EXIT_LINK } from 'amp-setup'; // From WP inline script.

/**
 * Internal dependencies
 */
import './setup.css';

import { AngleLeft } from './components/angle-left';
import { AngleRight } from './components/angle-right';
import { TechnicalBackground } from './pages/technical-background';
import { Stepper } from './components/stepper';
import { Goals } from './pages/goals';
import { TemplateMode } from './pages/template-mode';
import { ChooseReaderTheme } from './pages/choose-reader-theme';
import { SiteConfigurationSummary } from './pages/site-configuration-summary';
import { Save } from './pages/save';
import { SiteScan } from './pages/site-scan';
import { WordmarkLogo } from './components/wordmark-logo';

/**
 * Settings for the pages in the application.
 */
const PAGES = [
	{
		navTitle: __( 'Site Scan', 'amp' ),
		PageComponent: SiteScan,
	},
	{
		navTitle: __( 'Technical background', 'amp' ),
		PageComponent: TechnicalBackground,
	},
	{
		navTitle: __( 'Site goals', 'amp' ),
		PageComponent: Goals,
	},
	{
		navTitle: __( 'Template modes', 'amp' ),
		PageComponent: TemplateMode,
	},
	{
		navTitle: __( 'Reader themes', 'amp' ),
		PageComponent: ChooseReaderTheme,
	},
	{
		navTitle: __( 'Site configuration', 'amp' ),
		PageComponent: SiteConfigurationSummary,
	},
	{
		navTitle: __( 'Done', 'amp' ),
		PageComponent: Save,
	},
];

/**
 * Provides settings for a page at the given index.
 *
 * @param {number} index A page index.
 * @return {Object} Page object.
 */
function getPage( index ) {
	return PAGES[ index ];
}

/**
 * Setup root component.
 */
function Setup() {
	const [ activePageIndex, setActivePageIndex ] = useState( 0 );

	const { navTitle, PageComponent } = useMemo( () => getPage( activePageIndex ), [ activePageIndex ] );

	const moveForward = useCallback( () => {
		setActivePageIndex( activePageIndex + 1 );
	}, [ activePageIndex ] );

	const moveBack = useCallback( () => {
		setActivePageIndex( activePageIndex - 1 );
	}, [ activePageIndex ] );

	return (
		<div className="amp-setup-container">
			<div className="amp-setup">
				<div className="amp-stepper-container">
					<WordmarkLogo />
					<Stepper
						activePageIndex={ activePageIndex }
						pages={ PAGES }
					/>
				</div>
				<div className="amp-setup-panel-container">
					<Panel className="amp-setup-panel">
						<h1>
							{ navTitle }
						</h1>
						<PageComponent />
					</Panel>
					<div className="amp-setup-nav">
						<div className="amp-setup-nav__close">
							<Button isLink href={ EXIT_LINK }>
								{ __( 'Close', 'amp' ) }
							</Button>
						</div>
						<div className="amp-setup-nav__prev-next">
							{ 0 === activePageIndex
								? (
									<span className="amp-setup-nav__placeholder">
										{ ' ' }
									</span>
								)
								: (
									<Button
										className="amp-setup-nav__prev"
										onClick={ moveBack }
									>
										<AngleLeft className="amp-mobile-hide" />
										<span>
											{ __( 'Previous', 'amp' ) }
											{ ' ' }
											<span className="amp-mobile-hide">
												{ __( 'Step', 'amp' ) }
											</span>
										</span>
									</Button>
								)
							}
							{ PAGES.length - 1 === activePageIndex
								? (
									<span className="amp-setup-nav__placeholder">
										{ ' ' }
									</span>
								)
								: (
									<Button
										className="amp-setup-nav__next"
										onClick={ moveForward }
									>
										<span>
											{ __( 'Next', 'amp' ) }
											{ ' ' }
											<span className="amp-mobile-hide">
												{ __( 'Step', 'amp' ) }
											</span>
										</span>
										<AngleRight className="amp-mobile-hide" />
									</Button>
								)
							}
						</div>
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
