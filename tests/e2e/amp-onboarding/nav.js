/* eslint-disable jest/no-export */
/* eslint-disable jest/require-top-level-describe */

/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';
/**
 * Internal dependencies
 */
import { PREV_BUTTON_SELECTOR, clickNextButton, clickPrevButton } from './utils';

export function nav() {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup' );
		await page.waitForSelector( '.amp-setup-nav__prev-next' );
	} );

	test( 'should contain app root', async () => {
		await expect( page ).toMatchElement( '#amp-setup' );
	} );

	const getTitleText = async () => {
		await page.waitForSelector( 'h1' );
		return page.$eval( 'h1', ( el ) => el.innerText );
	};

	test( 'should have stepper items', async () => {
		await page.waitForSelector( '.amp-stepper__item' );
		const itemCount = await page.$$eval( '.amp-stepper__item', ( els ) => els.length );

		expect( itemCount ).toBe( 6 );
	} );

	test( 'should be navigable', async () => {
		let titleText;

		titleText = await getTitleText();
		expect( titleText ).toBe( 'Welcome to the Official AMP Plugin for WordPress' );

		await clickNextButton();
		titleText = await getTitleText();
		expect( titleText ).toBe( 'Are you technical?' );

		await page.$eval( '#technical-background-disable', ( el ) => el.click() );

		await clickNextButton();
		titleText = await getTitleText();
		expect( titleText ).toBe( 'Template modes' );

		await clickPrevButton();
		titleText = await getTitleText();
		expect( titleText ).toBe( 'Are you technical?' );
	} );

	test( 'hides prev button page one and two and disables next button on last page', async () => {
		const prevButton = await page.$( PREV_BUTTON_SELECTOR );
		expect( prevButton ).toBeNull();
	} );
}

/* eslint-enable jest/require-top-level-describe */
/* eslint-enable jest/no-export */
