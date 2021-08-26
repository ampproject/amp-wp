
/**
 * Internal dependencies
 */
import { testCloseButton, cleanUpSettings, moveToDoneScreen } from '../../utils/onboarding-wizard-utils';

async function testCommonReviewStepElements() {
	await expect( page ).toMatchElement( 'h1', { text: 'Your site is live!' } );
	await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
	await expect( page ).toMatchElement( 'h2', { text: 'Need help?' } );

	await expect( page ).toMatchElement( '.review__list' );
	await expect( '.review__list li' ).countToBe( 3 );
	await expect( page ).toMatchElement( '.review__list li', { text: /support forums/i } );
	await expect( page ).toMatchElement( '.review__list li', { text: /different template mode/i } );
	await expect( page ).toMatchElement( '.review__list li', { text: /how the PX plugin works/i } );

	await expect( page ).toMatchElement( '.review__preview-iframe' );

	const optionsCount = await page.$$eval( '.review__links-container input[type="radio"]', ( e ) => e.length );
	await expect( optionsCount ).not.toBe( 0 );

	const originalIframeSrc = await page.$eval( '.review__preview-iframe', ( e ) => e.getAttribute( 'src' ) );
	await expect( page ).toClick( '.review__links-container input[type="radio"]:not(:checked)' );
	const updatedIframeSrc = await page.$eval( '.review__preview-iframe', ( e ) => e.getAttribute( 'src' ) );
	expect( updatedIframeSrc ).not.toBe( originalIframeSrc );
}

describe( 'Review', () => {
	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'renders standard mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'standard' } );

		testCloseButton( { exists: false } );

		await testCommonReviewStepElements();

		await expect( page ).toMatchElement( 'p', { text: /standard mode/i } );
		await expect( '.review__preview-container input[type="checkbox"]' ).countToBe( 0 );
	} );

	it( 'renders transitional mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		await testCommonReviewStepElements();

		await expect( page ).toMatchElement( 'p', { text: /transitional mode/i } );
		await expect( '.review__preview-container input[type="checkbox"]:checked' ).countToBe( 1 );

		await page.waitForSelector( '.review__preview-iframe' );
		const originalIframeSrc = await page.$eval( '.review__preview-iframe', ( e ) => e.getAttribute( 'src' ) );
		await expect( page ).toClick( '.review__preview-container input[type="checkbox"]' );
		await page.waitForSelector( '.review__preview-iframe' );
		const updatedIframeSrc = await page.$eval( '.review__preview-iframe', ( e ) => e.getAttribute( 'src' ) );
		expect( updatedIframeSrc ).not.toBe( originalIframeSrc );

		await expect( '.review__preview-container input[type="checkbox"]:not(:checked)' ).countToBe( 1 );
	} );

	it( 'renders reader mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'reader' } );

		testCloseButton( { exists: true } );

		await testCommonReviewStepElements();

		await expect( page ).toMatchElement( 'p', { text: /reader mode/i } );
		await expect( page ).toMatchElement( '.review__preview-iframe' );
		await expect( '.review__preview-container input[type="checkbox"]' ).countToBe( 1 );
	} );
} );
