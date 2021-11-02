/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { AMP_PLUGINS } from 'amp-plugins'; // From WP inline script.
import { debounce } from 'lodash';

const ampPluginInstall = {

	/**
	 * Init function.
	 */
	init() {
		this.addAmpMessage();
		this.removeAdditionalInfo();
		this.addAmpMessageInSearchResult();
	},

	/**
	 * Check if "AMP Compatible" tab is open or not.
	 */
	isAmpCompatibleTab() {
		const queryParams = new URLSearchParams( window.location.search.substr( 1 ) );
		return queryParams.get( 'tab' ) === 'amp-compatible';
	},

	/**
	 * Add message for AMP Compatibility in AMP-compatible plugins card after search result comes in.
	 */
	addAmpMessageInSearchResult() {
		const pluginFilterForm = document.getElementById( 'plugin-filter' );
		const pluginInstallSearch = document.querySelector( '.plugin-install-php .wp-filter-search' );
		if ( ! pluginFilterForm || ! pluginInstallSearch ) {
			return;
		}

		let mutationObserver;

		const startSearchResults = debounce( () => {
			// Replace the class for our custom AMP-compatible tab once doing a search.
			const wrap = document.querySelector( '.plugin-install-tab-amp-compatible' );
			if ( wrap ) {
				wrap.classList.remove( 'plugin-install-tab-amp-compatible' );
				wrap.classList.add( 'plugin-install-tab-search-result' );
			}

			// Start watching for changes the first time a search is being made.
			if ( ! mutationObserver ) {
				mutationObserver = new MutationObserver( () => {
					this.addAmpMessage();
				} );
				mutationObserver.observe( pluginFilterForm, { childList: true } );
			}
		}, 1000 ); // See timeout in core: <https://github.com/WordPress/WordPress/blob/b87617e2719d114d123a88ed7e489170f0204735/wp-admin/js/updates.js#L2578>

		pluginInstallSearch.addEventListener( 'input', startSearchResults, { once: true } );
	},

	/**
	 * Add message for AMP Compatibility in AMP-compatible plugins card.
	 */
	addAmpMessage() {
		if ( this.isAmpCompatibleTab() ) {
			return;
		}

		for ( const pluginSlug of AMP_PLUGINS ) {
			const pluginCardElement = document.querySelector( `.plugin-card.plugin-card-${ pluginSlug }` );

			if ( ! pluginCardElement ) {
				continue;
			}

			// Skip cards that have already been processed.
			if ( pluginCardElement.classList.contains( 'amp-extension-card-message' ) ) {
				continue;
			}

			const messageElement = document.createElement( 'div' );
			const iconElement = document.createElement( 'span' );
			const tooltipElement = document.createElement( 'span' );

			messageElement.classList.add( 'amp-extension-card-message' );
			iconElement.classList.add( 'amp-logo-icon' );
			tooltipElement.classList.add( 'tooltiptext' );

			tooltipElement.append(
				__( 'This is known to work well with the AMP plugin.', 'amp' ),
			);

			messageElement.append( iconElement );
			messageElement.append( tooltipElement );
			messageElement.append( ' ' );

			pluginCardElement.appendChild( messageElement );
		}
	},

	/**
	 * Remove the additional info from the plugin card in the "AMP Compatible" tab.
	 */
	removeAdditionalInfo() {
		if ( this.isAmpCompatibleTab() ) {
			const pluginCardBottom = document.querySelectorAll( '.plugin-install-tab-amp-compatible .plugin-card-bottom' );

			if ( pluginCardBottom ) {
				for ( const elementNode of pluginCardBottom ) {
					elementNode.remove();
				}
			}
		}
	},
};

domReady( () => {
	ampPluginInstall.init();
} );
