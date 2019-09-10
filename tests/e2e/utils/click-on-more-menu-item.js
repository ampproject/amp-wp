/**
 * External dependencies
 */
import { first } from 'lodash';

/**
 * Clicks on More Tools & options menu item by label.
 * This is almost identical to the upstream clickOnMoreMenuItem but uses the more reliable page.evaluate for clicking.
 *
 * @param {string} buttonLabel Aria label.
 */
export async function clickOnMoreMenuItem( buttonLabel ) {
	await page.click( '.edit-post-more-menu [aria-label="More tools & options"]' );
	const moreMenuContainerSelector =
		'//*[contains(concat(" ", @class, " "), " edit-post-more-menu__content ")]';
	let elementToClick = first( await page.$x(
		`${ moreMenuContainerSelector }//button[contains(text(), "${ buttonLabel }")]`
	) );
	// If button is not found, the label should be on the info wrapper.
	if ( ! elementToClick ) {
		elementToClick = first( await page.$x(
			moreMenuContainerSelector +
			'//button' +
			`/*[contains(concat(" ", @class, " "), " components-menu-item__info-wrapper ")][contains(text(), "${ buttonLabel }")]`
		) );
	}

	await page.evaluate( ( el ) => {
		el.click();
	}, elementToClick );
}
