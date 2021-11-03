/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';

/**
 * External dependencies
 */
import { AMP_COMPATIBLE } from 'amp-themes'; // From WP inline script.

/**
 * Internal dependencies
 */
import ampViewTheme from './theme-install/view/theme';

const ampThemeInstall = {

	/**
	 * Init function.
	 */
	init() {
		this.addTab();
		this.overrideViews();
	},

	/**
	 * Add a new tab for AMP-compatible themes on theme install page.
	 */
	addTab() {
		const filterLinks = document.querySelector( '.filter-links' );
		if ( ! filterLinks ) {
			return;
		}

		const listItem = document.createElement( 'li' );
		const anchorElement = document.createElement( 'a' );

		anchorElement.setAttribute( 'href', '#' );
		anchorElement.setAttribute( 'data-sort', AMP_COMPATIBLE );
		anchorElement.append( __( 'AMP Compatible', 'amp' ) );

		listItem.appendChild( anchorElement );

		filterLinks.appendChild( listItem );
	},

	/**
	 * Override theme view.
	 */
	overrideViews() {
		wp.themes.view.Theme = ampViewTheme;
	},

};

domReady( () => {
	ampThemeInstall.init();
} );
