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
 * Get session storage key for storing the previously-fetched count.
 *
 * @param {string} itemId Menu item ID.
 * @return {string} Session storage key.
 */
function getSessionStorageKey(itemId) {
	return `${itemId}-last-count`;
}

/**
 * Sets the loading state on a menu item.
 *
 * @param {string} itemId Menu item ID.
 */
function setMenuItemIsLoading(itemId) {
	const itemEl = document.getElementById(itemId);

	if (!itemEl || itemEl.querySelector('.amp-count-loading')) {
		return;
	}

	// Add a loading spinner if we haven't obtained the count before or the last count was not zero.
	const lastCount = sessionStorage.getItem(getSessionStorageKey(itemId));
	if (!lastCount || '0' !== lastCount) {
		const loadingEl = document.createElement('span');
		loadingEl.classList.add('amp-count-loading');
		itemEl.append(loadingEl);

		itemEl.classList.add('awaiting-mod');
	}
}

/**
 * Sets a menu item count value.
 *
 * If the count is not a number or is `0`, the element that contains the count is instead removed (as it would be no
 * longer relevant).
 *
 * @param {string} itemId Menu item ID.
 * @param {number} count  Count to set.
 */
function setMenuItemCountValue(itemId, count) {
	const itemEl = document.getElementById(itemId);

	if (!itemEl) {
		return;
	}

	if (isNaN(count) || count === 0) {
		itemEl.parentNode.removeChild(itemEl);
		sessionStorage.setItem(getSessionStorageKey(itemId), '0');
	} else {
		const text = count.toLocaleString();
		itemEl.textContent = text;
		itemEl.classList.add('awaiting-mod'); // In case it wasn't set in setMenuItemIsLoading().
		sessionStorage.setItem(getSessionStorageKey(itemId), text);
	}
}

/**
 * Initializes the 'Validated URLs' and 'Error Index' menu items.
 */
function initializeMenuItemCounts() {
	setMenuItemIsLoading('amp-new-error-index-count');
	setMenuItemIsLoading('amp-new-validation-url-count');
}

/**
 * Updates the 'Validated URLs' and 'Error Index' menu items with their respective unreviewed count.
 *
 * @param {Object} counts                Counts for menu items.
 * @param {number} counts.validated_urls Unreviewed validated URLs count.
 * @param {number} counts.errors         Unreviewed validation errors count.
 */
function updateMenuItemCounts(counts) {
	const { validated_urls: newValidatedUrlCount, errors: newErrorCount } =
		counts;

	setMenuItemCountValue('amp-new-error-index-count', newErrorCount);
	setMenuItemCountValue('amp-new-validation-url-count', newValidatedUrlCount);
}

/**
 * Requests validation counts.
 */
function fetchValidationCounts() {
	apiFetch({ path: '/amp/v1/unreviewed-validation-counts' })
		.then((counts) => {
			updateMenuItemCounts(counts);
		})
		.catch((error) => {
			updateMenuItemCounts({ validated_urls: 0, errors: 0 });

			const message =
				error?.message ||
				__(
					'An unknown error occurred while retrieving the validation counts',
					'amp'
				);
			// eslint-disable-next-line no-console
			console.error(`[AMP Plugin] ${message}`);
		});
}

/**
 * Fetches the validation counts only when the AMP submenu is open for the first time.
 *
 * @param {HTMLElement} root AMP submenu item.
 */
function createObserver(root) {
	// IntersectionObserver is not available in IE11, so just hide the counts entirely for that browser.
	if (!('IntersectionObserver' in window)) {
		updateMenuItemCounts({ validated_urls: 0, errors: 0 });
		return;
	}

	const target = root.querySelector('ul');

	const observer = new IntersectionObserver(
		([entry]) => {
			if (!entry || !entry.isIntersecting) {
				return;
			}

			observer.unobserve(target);

			fetchValidationCounts();
		},
		{ root }
	);

	observer.observe(target);
}

domReady(() => {
	const ampMenuItem = document.getElementById('toplevel_page_amp-options');

	// Bail if the AMP submenu is not in the DOM.
	if (!ampMenuItem) {
		return;
	}

	initializeMenuItemCounts();

	// If the AMP submenu is opened, fetch validation counts as soon as possible. Thanks to the preload middleware for
	// `wp.apiFetch`, the validation count data should be available right away, so no actual HTTP request will be made.
	if (ampMenuItem.classList.contains('wp-menu-open')) {
		fetchValidationCounts();
	} else {
		createObserver(ampMenuItem);
	}
});
