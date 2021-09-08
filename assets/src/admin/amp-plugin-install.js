/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { AMP_PLUGINS, NONE_WPORG_PLUGINS } from 'amp-plugins'; // From WP inline script.
import jQuery from 'jquery';

const ampPluginInstall = {

	/**
	 * Init function.
	 */
	init() {
		this.addAmpMessage();
		this.removeAdditionalInfo();
	},

	/**
	 * Add AMP compatible message in AMP compatible plugin card.
	 */
	addAmpMessage() {
		// eslint-disable-next-line guard-for-in
		for ( const index in AMP_PLUGINS ) {
			const pluginSlug = AMP_PLUGINS[ index ];
			const pluginCardElement = document.querySelector( `.plugin-card.plugin-card-${ pluginSlug }` );

			if ( ! pluginCardElement ) {
				continue;
			}

			const messageElement = document.createElement( 'div' );
			const iconElement = document.createElement( 'span' );
			const tooltipElement = document.createElement( 'span' );

			messageElement.classList.add( 'extension-card-px-message' );
			iconElement.classList.add( 'amp-logo-icon' );
			tooltipElement.classList.add( 'tooltiptext' );

			tooltipElement.append(
				__( 'This theme follow best practice and is known to work well with AMP plugin.', 'amp' ),
			);

			messageElement.append( iconElement );
			messageElement.append( tooltipElement );
			messageElement.append( ' ' );
			messageElement.append( __( 'Page Experience Enhancing', 'amp' ) );

			jQuery( pluginCardElement ).append( messageElement );
		}
	},

	removeAdditionalInfo() {
		// eslint-disable-next-line guard-for-in
		for ( const index in NONE_WPORG_PLUGINS ) {
			const pluginSlug = NONE_WPORG_PLUGINS[ index ];
			const pluginCardElement = document.querySelector( `.plugin-card.plugin-card-${ pluginSlug }` );

			if ( ! pluginCardElement ) {
				continue;
			}

			jQuery( '.plugin-card-bottom', pluginCardElement ).remove();
		}
	},
};

domReady( () => {
	ampPluginInstall.init();
} );
