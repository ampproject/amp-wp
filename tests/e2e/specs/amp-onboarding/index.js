
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Setup Screen', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup&amp-new-onboarding=1' );
	} );

	it( 'should contain app root', async () => {
		await expect( page ).toMatchElement( '#amp-setup' );
	} );

	const getTitleText = () => page.$eval( 'h1', ( el ) => el.innerText );

	it( 'should be navigable', async () => {
		let titleText;

		titleText = await getTitleText();
		expect( titleText ).toBe( 'Site scan' );

		await page.waitForSelector( '.amp-setup-nav__next:not([disabled])' );
		await page.click( '.amp-setup-nav__next' );
		titleText = await getTitleText();
		expect( titleText ).toBe( 'Technical background' );

		await page.click( '.amp-setup-nav__prev' );
		titleText = await getTitleText();
		expect( titleText ).toBe( 'Site scan' );
	} );

	it( 'hides prev button page one and disables next button on last page', async () => {
		// On page 1 of 7.
		let prevButton = await page.$( '.amp-setup-nav__prev' );
		expect( prevButton ).toBeNull();

		await page.waitForSelector( '.amp-setup-nav__next:not([disabled])' );
		await page.click( '.amp-setup-nav__next' ); // 2 of 7.

		prevButton = await page.$( '.amp-setup-nav__prev' );
		expect( prevButton ).not.toBeNull();

		let disabledNextButton = await page.$( '.amp-setup-nav__next[disabled]' );
		expect( disabledNextButton ).toBeNull();

		// Click to last page.
		await page.waitForSelector( '.amp-setup-nav__next:not([disabled])' );
		await page.click( '.amp-setup-nav__next' ); // 3 of 7.
		await page.waitForSelector( '.amp-setup-nav__next:not([disabled])' );
		await page.click( '.amp-setup-nav__next' ); // 4 of 7.
		await page.waitForSelector( '.amp-setup-nav__next:not([disabled])' );
		await page.click( '.amp-setup-nav__next' ); // 5 of 7.
		await page.waitForSelector( '.amp-setup-nav__next:not([disabled])' );
		await page.click( '.amp-setup-nav__next' ); // 6 of 7.
		await page.waitForSelector( '.amp-setup-nav__next:not([disabled])' );
		await page.click( '.amp-setup-nav__next' ); // 7 of 7.

		disabledNextButton = await page.$( '.amp-setup-nav__next[disabled]' );
		expect( disabledNextButton ).not.toBeNull();
	} );

	it( 'should have stepper items', async () => {
		const itemCount = await page.$$eval( '.amp-stepper__item', ( els ) => els.length );

		expect( itemCount ).toBe( 7 );
	} );
} );
