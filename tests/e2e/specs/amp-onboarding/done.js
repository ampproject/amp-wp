
/**
 * Internal dependencies
 */
import { testCloseButton, cleanUpSettings, moveToDoneScreen } from '../../utils/onboarding-wizard-utils';

async function testCommonDoneStepElements() {
	await expect( page ).toMatchElement( 'h1', { text: 'Done' } );
	await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
	await expect( page ).toMatchElement( 'h2', { text: 'Need help?' } );

	await expect( page ).toMatchElement( '.done__list' );
	await expect( '.done__list li' ).countToBe( 3 );
	await expect( page ).toMatchElement( '.done__list li', { text: /support forums/i } );
	await expect( page ).toMatchElement( '.done__list li', { text: /different template mode/i } );
	await expect( page ).toMatchElement( '.done__list li', { text: /how the AMP plugin works/i } );

	await expect( page ).toMatchElement( '.done__preview-iframe' );

	await expect( '.done__links-container a' ).not.countToBe( 0 );

	const originalIframeSrc = await page.$eval( '.done__preview-iframe', ( e ) => e.getAttribute( 'src' ) );

	await expect( page ).toClick( '.done__links-container a:not([class*="--active"])' );

	const updatedIframeSrc = await page.$eval( '.done__preview-iframe', ( e ) => e.getAttribute( 'src' ) );

	expect( updatedIframeSrc ).not.toBe( originalIframeSrc );
}

describe( 'Done', () => {
	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'renders standard mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'standard' } );

		testCloseButton( { exists: false } );

		await testCommonDoneStepElements();

		await expect( page ).toMatchElement( 'p', { text: /Standard mode/i } );
		await expect( '.done__preview-container input[type="checkbox"]' ).countToBe( 0 );
	} );

	it( 'renders transitional mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		await testCommonDoneStepElements();

		await expect( page ).toMatchElement( 'p', { text: /Transitional mode/i } );
		await expect( '.done__preview-container input[type="checkbox"]:checked' ).countToBe( 1 );

		await page.waitForSelector( '.done__preview-iframe' );
		const originalIframeSrc = await page.$eval( '.done__preview-iframe', ( e ) => e.getAttribute( 'src' ) );

		await expect( page ).toClick( '.done__preview-container input[type="checkbox"]' );

		await page.waitForSelector( '.done__preview-iframe' );
		const updatedIframeSrc = await page.$eval( '.done__preview-iframe', ( e ) => e.getAttribute( 'src' ) );

		expect( updatedIframeSrc ).not.toBe( originalIframeSrc );

		await expect( '.done__preview-container input[type="checkbox"]:not(:checked)' ).countToBe( 1 );
	} );

	it( 'renders reader mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'reader' } );

		testCloseButton( { exists: true } );

		await testCommonDoneStepElements();

		await expect( page ).toMatchElement( 'p', { text: /Reader mode/i } );
		await expect( page ).toMatchElement( '.done__preview-iframe' );

		await expect( '.done__preview-container input[type="checkbox"]' ).countToBe( 1 );
	} );
} );
