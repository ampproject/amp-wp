/**
 * External dependencies
 */
import { isPlainObject } from 'lodash';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Updates a menu item with its count.
 *
 * If the count is not a number or is `0`, the element that contains the count is instead removed (as it would be no longer relevant).
 *
 * @param {HTMLElement} itemEl Menu item element.
 * @param {number} count Count to set.
 */
function updateMenuItem( itemEl, count ) {
	count = Math.abs( count );

	if ( isNaN( count ) || count === 0 ) {
		itemEl.parentNode.parentNode.removeChild( itemEl.parentNode );
	} else {
		itemEl.classList.remove( 'loading' );
		itemEl.textContent = count.toLocaleString();
	}
}

/**
 * Updates the 'Validated URLs' and 'Error Index' menu items with their respective unreviewed count.
 *
 * @param {any} counts Counts for menu items.
 * @param {number} counts.validated_urls Unreviewed validated URLs count.
 * @param {number} counts.errors Unreviewed validation errors count.
 */
function updateMenuItemCounts( counts ) {
	if ( ! isPlainObject( counts ) ) {
		// eslint-disable-next-line no-console
		console.error( '[AMP Plugin] An error occurred while retrieving unreviewed validation counts. Received: ' + JSON.stringify( counts ) );
		counts = {};
	}

	const { validated_urls: newValidatedUrlCount, errors: newErrorCount } = counts;

	const errorCountEl = document.getElementById( 'new-error-index-count' );
	const validatedUrlsCountEl = document.getElementById( 'new-validation-url-count' );

	updateMenuItem( errorCountEl, newErrorCount );
	updateMenuItem( validatedUrlsCountEl, newValidatedUrlCount );
}

domReady( async () => {
	const counts = await apiFetch( { path: '/amp/v1/unreviewed-validation-counts' } );
	updateMenuItemCounts( counts );
} );
