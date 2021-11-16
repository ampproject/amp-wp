/**
 * Internal dependencies
 */
import {
	testCloseButton,
	cleanUpSettings,
	moveToDoneScreen,
	scrollToElement,
} from '../../utils/onboarding-wizard-utils';

async function testCommonDoneStepElements() {
	await expect( page ).toMatchElement( 'h1', { text: 'Done' } );
	await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
	await expect( page ).toMatchElement( 'h2', { text: 'Need help?' } );

	await expect( page ).toMatchElement( '.done__list' );
	await expect( '.done__list li' ).countToBe( 3 );
	await expect( page ).toMatchElement( '.done__list li', { text: /support forums/i } );
	await expect( page ).toMatchElement( '.done__list li', { text: /different template mode/i } );
	await expect( page ).toMatchElement( '.done__list li', { text: /how the AMP plugin works/i } );

	await expect( page ).toMatchElement( 'p', { text: /Browse your site/i } );
	await expect( page ).toMatchElement( '.done__preview-iframe' );

	await expect( '.done__links-container a' ).not.countToBe( 0 );

	const originalIframeSrc = await page.$eval( '.done__preview-iframe', ( e ) => e.getAttribute( 'src' ) );

	await Promise.all( [
		scrollToElement( { selector: '.done__links-container a:not([class*="--active"])', click: true } ),
		page.waitForXPath( `//iframe[@class="done__preview-iframe"][not(@src="${ originalIframeSrc }")]` ),
	] );

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
		await expect( page ).not.toMatchElement( '.done__preview-container input[type="checkbox"]' );
	} );

	it( 'renders transitional mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		await testCommonDoneStepElements();

		await expect( page ).toMatchElement( 'p', { text: /Transitional mode/i } );
		await expect( page ).toMatchElement( '.done__preview-container input[type="checkbox"]:checked' );

		const originalIframeSrc = await page.$eval( '.done__preview-iframe', ( e ) => e.getAttribute( 'src' ) );

		await Promise.all( [
			scrollToElement( { selector: '.done__preview-container input[type="checkbox"]', click: true } ),
			page.waitForXPath( `//iframe[@class="done__preview-iframe"][not(@src="${ originalIframeSrc }")]` ),
		] );

		const updatedIframeSrc = await page.$eval( '.done__preview-iframe', ( e ) => e.getAttribute( 'src' ) );

		expect( updatedIframeSrc ).not.toBe( originalIframeSrc );

		await expect( page ).toMatchElement( '.done__preview-container input[type="checkbox"]:not(:checked)' );
	} );

	it( 'renders reader mode site review screen', async () => {
		await moveToDoneScreen( { mode: 'reader' } );

		testCloseButton( { exists: true } );

		await testCommonDoneStepElements();

		await expect( page ).toMatchElement( 'p', { text: /Reader mode/i } );
		await expect( page ).toMatchElement( '.done__preview-container input[type="checkbox"]' );
	} );
} );
