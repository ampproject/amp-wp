/**
 * External dependencies
 */
import { isPlainObject } from 'lodash';

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
	const { validated_urls: newValidatedUrlCount, errors: newErrorCount } = counts;

	const errorCountEl = document.getElementById( 'new-error-index-count' );
	const validatedUrlsCountEl = document.getElementById( 'new-validation-url-count' );

	updateMenuItem( errorCountEl, newErrorCount );
	updateMenuItem( validatedUrlsCountEl, newValidatedUrlCount );
}

/**
 * Fetches the validation counts only when the AMP submenu is open for the first time.
 *
 * @param {HTMLElement} root AMP submenu item.
 */
function createObserver( root ) {
	// let hasIntersected = false;

	const observer = new IntersectionObserver( ( entries ) => {
		if ( ! entries[ 0 ].isIntersecting ) {
			return;
		}

		observer.unobserve( entries[ 0 ].target );

		apiFetch( { path: '/amp/v1/unreviewed-validation-counts' } ).then( ( counts ) => {
			updateMenuItemCounts( counts );
		} ).catch( ( error ) => {
			updateMenuItemCounts( { validated_urls: 0, errors: 0 } );

			const message = error?.message || __( 'An unknown error occurred while retrieving the validation counts', 'amp' );
			// eslint-disable-next-line no-console
			console.error( `[AMP Plugin] ${ message }` );
		} );
	}, { root } );

	const target = root.querySelector( 'ul' );
	observer.observe( target );
}

domReady( () => {
	const ampMenuItem = document.getElementById( 'toplevel_page_amp-options' );

	// Bail if the AMP submenu is not in the DOM.
	if ( ! ampMenuItem ) {
		return;
	}

	createObserver( ampMenuItem );
} );
