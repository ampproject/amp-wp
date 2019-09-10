/**
 * WordPress dependencies
 */
import { createNewPost, saveDraft } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, selectBlockByClassName, getBlocksOnPage } from '../../utils';

const textBlockClass = 'wp-block-amp-story-text';
const fontPickerID = 'autocomplete__input';

describe( 'Font picker in Text Block', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
		await page.waitForSelector( `.${ textBlockClass }.is-not-editing` );
		await selectBlockByClassName( textBlockClass );
		const textToWrite = 'Hello';

		await page.click( `.${ textBlockClass }` );
		await page.keyboard.type( textToWrite );
		await page.$eval( '.block-editor-block-list__layout .block-editor-block-list__block .wp-block-amp-amp-story-text', ( node ) => node.textContent );

		await page.click( `.${ fontPickerID }` );
	} );

	it( 'should be able to search for ubuntu font', async () => {
		await page.keyboard.type( 'Arimo' );

		const nodes = await page.$x(
			'//ul[contains(@class,"autocomplete__menu")]//li'
		);
		expect( nodes ).toHaveLength( 1 );
		await expect( page ).toMatchElement( '#arimo-font' );
	} );

	it( 'should be able to search for Arial font and get multi results', async () => {
		await page.keyboard.type( 'pt sans' );

		const nodes = await page.$x(
			'//ul[contains(@class,"autocomplete__menu")]//li'
		);
		expect( nodes ).toHaveLength( 3 );
		await expect( page ).toMatchElement( '#pt-sans-font' );
		await expect( page ).toMatchElement( '#pt-sans-narrow-font' );
		await expect( page ).toMatchElement( '#pt-sans-caption-font' );
	} );

	it( 'should be able to search for none existing font', async () => {
		await page.keyboard.type( 'Wibble' );
		expect( await page.evaluate( () => {
			return document.querySelector( '.autocomplete__option--no-results' ).innerHTML;
		} ) ).toContain( 'No font found' );
	} );

	it( 'should be able to search for ubuntu font and select font', async () => {
		await page.keyboard.type( 'Ubuntu' );

		await page.waitForSelector( '.autocomplete__option' );
		await page.click( '.autocomplete__option' );
		const textBlockBefore = ( await getBlocksOnPage() )[ 0 ];
		expect( textBlockBefore.attributes.ampFontFamily ).toStrictEqual( 'Ubuntu' );
	} );

	it( 'should be able to search for ubuntu font and select font with keyboard', async () => {
		await page.keyboard.type( 'Ubuntu' );

		await page.waitForSelector( '.autocomplete__option' );
		await page.keyboard.press( 'ArrowDown' );
		await page.keyboard.press( 'Enter' );
		const textBlockBefore = ( await getBlocksOnPage() )[ 0 ];
		expect( textBlockBefore.attributes.ampFontFamily ).toStrictEqual( 'Ubuntu' );
	} );

	it( 'should be able to search for ubuntu font and remove font', async () => {
		await page.keyboard.type( 'Ubuntu' );

		await page.waitForSelector( '.autocomplete__option' );
		await page.click( '.autocomplete__option' );

		await page.waitForSelector( '.autocomplete__icon' );
		await page.click( '.autocomplete__icon' );
		const textBlockBefore = ( await getBlocksOnPage() )[ 0 ];
		expect( textBlockBefore.attributes.ampFontFamily ).toBeNull();
	} );

	it( 'should be able to search for ubuntu font and save post', async () => {
		await page.keyboard.type( 'Ubuntu' );

		await page.waitForSelector( '.autocomplete__option' );
		await page.click( '.autocomplete__option' );

		await saveDraft();
		await page.reload();

		const textBlockBefore = ( await getBlocksOnPage() )[ 0 ];
		expect( textBlockBefore.attributes.ampFontFamily ).toStrictEqual( 'Ubuntu' );
	} );
} );
