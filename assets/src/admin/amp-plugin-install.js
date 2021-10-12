/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { AMP_PLUGINS, NONE_WPORG_PLUGINS } from 'amp-plugins'; // From WP inline script.
import { debounce } from 'lodash';

const ampPluginInstall = {

	/**
	 * Init function.
	 */
	init() {
		this.addAmpMessage();
		this.removeAdditionalInfo();
		this.addAMPMessageInSearchResult();
	},

	/**
	 * Add message for AMP Compatibility in AMP-compatible plugins card after search result comes in.
	 */
	addAMPMessageInSearchResult() {
		const pluginInstallSearch = document.querySelector( '.plugin-install-php .wp-filter-search' );

		if ( pluginInstallSearch ) {
			const callback = debounce( () => {
				if ( 'undefined' !== typeof wp.updates.searchRequest ) {
					wp.updates.searchRequest.done( () => {
						this.addAmpMessage();
					} );
				}
			}, 1500 );

			pluginInstallSearch.addEventListener( 'keyup', callback );
			pluginInstallSearch.addEventListener( 'input', callback );
		}
	},

	/**
	 * Add message for AMP Compatibility in AMP-compatible plugins card.
	 */
	addAmpMessage() {
		// eslint-disable-next-line guard-for-in
		for ( const pluginSlug of AMP_PLUGINS ) {
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
				__( 'This plugin follow best practice and is known to work well with AMP plugin.', 'amp' ),
			);

			messageElement.append( iconElement );
			messageElement.append( tooltipElement );
			messageElement.append( ' ' );
			messageElement.append( __( 'AMP Compatible', 'amp' ) );

			pluginCardElement.appendChild( messageElement );
		}
	},

	/**
	 * Remove the additional info from plugin card if plugin is none wporg plugin.
	 */
	removeAdditionalInfo() {
		// eslint-disable-next-line guard-for-in
		for ( const pluginSlug of NONE_WPORG_PLUGINS ) {
			const pluginCardElement = document.querySelector( `.plugin-card.plugin-card-${ pluginSlug }` );

			if ( ! pluginCardElement ) {
				continue;
			}

			const pluginCardBottom = pluginCardElement.querySelector( '.plugin-card-bottom' );
			if ( pluginCardBottom ) {
				pluginCardBottom.remove();
			}
		}
	},
};

domReady( () => {
	ampPluginInstall.init();
} );
