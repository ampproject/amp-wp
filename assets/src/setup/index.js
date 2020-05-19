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
import { TechnicalBackground } from './pages/technical-background';
import { Stepper } from './components/stepper';
import { Goals } from './pages/goals';

import './setup.css';

const PAGES = [
	{
		navTitle: __( 'Technical Background', 'amp' ),
		PageComponent: TechnicalBackground,
	},
	{
		navTitle: __( 'Site Goals', 'amp' ),
		PageComponent: Goals,
	},
];

function getPage( index ) {
	return PAGES[ index ];
}

function Setup() {
	const [ activePageIndex, setActivePageIndex ] = useState( 0 );

	const { PageComponent } = useMemo( () => getPage( activePageIndex ), [ activePageIndex ] );

	return (
		<div>
			<Stepper
				activePageIndex={ activePageIndex }
				pages={ PAGES }
			/>
			<div className="page">
				<PageComponent />
			</div>
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
	);
}

domReady( () => {
	const root = document.getElementById( APP_ROOT_ID );

	if ( root ) {
		render( <Setup />, root );
	}
} );
