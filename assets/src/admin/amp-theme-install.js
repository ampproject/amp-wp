/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';

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
		anchorElement.setAttribute( 'data-sort', 'amp-compatible' );
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
