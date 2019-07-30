/**
 * WordPress dependencies
 */
import { createNewPost, clickButton, saveDraft } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, insertBlock } from '../../utils';

describe( 'Story Animations', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	it( 'should save correct animation values', async () => {
		await createNewPost( { postType: 'amp_story' } );
		// Add Author block with animation.
		await insertBlock( 'Author' );
		page.waitForSelector( '.components-select-control__input' );
		await page.select( '.components-select-control__input', 'fly-in-bottom' );
		page.waitForSelector( 'input[aria-label="Delay (ms)"]' );
		await page.type( 'input[aria-label="Delay (ms)"]', '15' );

		// Add Date block with animation.
		await insertBlock( 'Date' );
		page.waitForSelector( '.components-select-control__input' );
		await page.select( '.components-select-control__input', 'pulse' );
		page.waitForSelector( 'label[for="amp-stories-animation-order-picker"]' );
		await clickButton( 'Immediately' );
		page.waitForSelector( '.components-animate__appear button[aria-checked="false"]' );
		await page.click( '.components-animate__appear button[aria-checked="false"]' );
		await saveDraft();
		await page.reload();

		page.waitForSelector( '.wp-block-amp-amp-story-post-author' );

		// We have to select the page first and then the block inside.
		await page.click( '.amp-page-active' );
		await page.click( '.wp-block-amp-amp-story-post-author' );
		page.waitForSelector( 'input[aria-label="Delay (ms)"]' );

		expect( await page.evaluate( () => {
			return document.querySelector( 'input[aria-label="Delay (ms)"]' ).value;
		} ) ).toBe( '15' );

		expect( await page.evaluate( () => {
			return document.querySelector( '.components-select-control__input [value="fly-in-bottom"]' ).selected;
		} ) ).toBe( true );

		await page.click( '.amp-page-active' );
		await page.click( '.wp-block-amp-amp-story-post-date' );
		page.waitForSelector( '.components-select-control__input [value="pulse"]' );

		expect( await page.evaluate( () => {
			return document.querySelector( '.components-select-control__input [value="pulse"]' ).selected;
		} ) ).toBe( true );

		expect( await page.evaluate( () => {
			return document.querySelector( 'button[aria-label="Begin after: Story Author"]' ).innerHTML;
		} ) ).toContain( 'admin' );
	} );

	it( 'should not allow creating a cycle in animation after', async () => {
		await createNewPost( { postType: 'amp_story' } );
		// Add Author block with animation.
		await insertBlock( 'Author' );
		await page.select( '.components-select-control__input', 'fly-in-bottom' );
		await page.type( 'input[aria-label="Delay (ms)"]', '15' );

		// Add Date block with animation.
		await insertBlock( 'Date' );
		await page.select( '.components-select-control__input', 'pulse' );
		page.waitForSelector( 'label[for="amp-stories-animation-order-picker"]' );
		await clickButton( 'Immediately' );

		page.waitForSelector( '.components-animate__appear button[aria-checked="false"]' );
		await page.click( '.components-animate__appear button[aria-checked="false"]' );

		await page.click( '.amp-page-active' );
		await page.click( '.wp-block-amp-amp-story-post-author' );
		page.waitForSelector( 'label[for="amp-stories-animation-order-picker"]' );
		await clickButton( 'Immediately' );
		page.waitForSelector( '.components-animate__appear' );

		expect( await page.evaluate( () => {
			return document.querySelector( '.components-animate__appear button[aria-checked="false"]' );
		} ) ).toBeNull();
	} );
} );
