/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Updates a menu item with its count.
 *
 * If the count is not a number or is `0`, the element that contains the count is instead removed (as it would be no
 * longer relevant). If the count is -1, a loading indicator will be added to the menu item.
 *
 * @param {string} itemId Menu item ID.
 * @param {number} count  Count to set.
 */
function updateMenuItem( itemId, count ) {
	const itemEl = document.getElementById( itemId );

	if ( ! itemEl ) {
		return;
	}

	if ( isNaN( count ) || count === 0 ) {
		itemEl.parentNode.removeChild( itemEl );
	} else if ( count > 0 ) {
		itemEl.textContent = count.toLocaleString();
	} else if ( count === -1 && ! itemEl.querySelector( '.amp-count-loading' ) ) {
		const loadingEl = document.createElement( 'span' );

		loadingEl.classList.add( 'amp-count-loading' );
		itemEl.classList.add( 'awaiting-mod' );

		itemEl.append( loadingEl );
	}
}

/**
 * Initializes the 'Validated URLs' and 'Error Index' menu items.
 */
function initializeMenuItemCounts() {
	updateMenuItem( 'new-error-index-count', -1 );
	updateMenuItem( 'new-validation-url-count', -1 );
}

/**
 * Updates the 'Validated URLs' and 'Error Index' menu items with their respective unreviewed count.
 *
 * @param {Object} counts                Counts for menu items.
 * @param {number} counts.validated_urls Unreviewed validated URLs count.
 * @param {number} counts.errors         Unreviewed validation errors count.
 */
function updateMenuItemCounts( counts ) {
	const { validated_urls: newValidatedUrlCount, errors: newErrorCount } = counts;

	updateMenuItem( 'new-error-index-count', newErrorCount );
	updateMenuItem( 'new-validation-url-count', newValidatedUrlCount );
}

/**
 * Requests validation counts.
 */
function fetchValidationCounts() {
	apiFetch( { path: '/amp/v1/unreviewed-validation-counts' } ).then( ( counts ) => {
		updateMenuItemCounts( counts );
	} ).catch( ( error ) => {
		updateMenuItemCounts( { validated_urls: 0, errors: 0 } );

		const message = error?.message || __( 'An unknown error occurred while retrieving the validation counts', 'amp' );
		// eslint-disable-next-line no-console
		console.error( `[AMP Plugin] ${ message }` );
	} );
}

/**
 * Fetches the validation counts only when the AMP submenu is open for the first time.
 *
 * @param {HTMLElement} root AMP submenu item.
 */
function createObserver( root ) {
	// IntersectionObserver is not available in IE11, so just hide the counts entirely for that browser.
	if ( ! ( 'IntersectionObserver' in window ) ) {
		updateMenuItemCounts( { validated_urls: 0, errors: 0 } );
		return;
	}

	const target = root.querySelector( 'ul' );

	const observer = new IntersectionObserver( ( [ entry ] ) => {
		if ( ! entry || ! entry.isIntersecting ) {
			return;
		}

		observer.unobserve( target );

		fetchValidationCounts();
	}, { root } );

	observer.observe( target );
}

domReady( () => {
	const ampMenuItem = document.getElementById( 'toplevel_page_amp-options' );

	// Bail if the AMP submenu is not in the DOM.
	if ( ! ampMenuItem ) {
		return;
	}

	// If the AMP submenu is opened, fetch validation counts as soon as possible. Thanks to the preload middleware for
	// `wp.apiFetch`, the validation count data should be available right away, so no actual HTTP request will be made.
	if ( ampMenuItem.classList.contains( 'wp-menu-open' ) ) {
		initializeMenuItemCounts();
		fetchValidationCounts();

		return;
	}

	initializeMenuItemCounts( true );
	createObserver( ampMenuItem );
} );
