/**
 * WordPress dependencies
 */
import { createNewPost, clickButton, saveDraft } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, insertBlock, selectBlockByClassName } from '../../utils';

describe( 'Story Animations', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
	} );

	it( 'should save correct animation values', async () => {
		// Add Author block with animation.
		await insertBlock( 'Author' );
		await page.waitForSelector( '.components-select-control__input' );
		await page.select( '.components-select-control__input', 'fly-in-bottom' );
		await page.waitForSelector( 'input[aria-label="Delay (ms)"]' );
		await page.type( 'input[aria-label="Delay (ms)"]', '15' );

		await saveDraft();
		await page.reload();

		await page.waitForSelector( '.wp-block-amp-amp-story-post-author' );

		await selectBlockByClassName( 'wp-block-amp-amp-story-post-author' );
		await page.waitForSelector( 'input[aria-label="Delay (ms)"]' );

		expect( await page.evaluate( () => {
			return document.querySelector( 'input[aria-label="Delay (ms)"]' ).value;
		} ) ).toBe( '15' );

		expect( await page.evaluate( () => {
			return document.querySelector( '.components-select-control__input [value="fly-in-bottom"]' ).selected;
		} ) ).toBe( true );
	} );

	it( 'should save correct animation after values', async () => {
		// Add Author block with animation.
		await insertBlock( 'Author' );
		await page.waitForSelector( '.components-select-control__input' );
		await page.select( '.components-select-control__input', 'fly-in-bottom' );

		// Add Date block with animation.
		await insertBlock( 'Date' );
		await page.waitForSelector( '.components-select-control__input' );
		await page.select( '.components-select-control__input', 'pulse' );

		// Add Author block as Begin After.
		await page.waitForSelector( 'button[aria-label="Begin immediately"]' );
		await clickButton( 'Immediately' );

		page.waitForSelector( '.components-animate__appear button[aria-checked="false"]' );
		await page.click( '.components-animate__appear button[aria-checked="false"]' );
		await saveDraft();
		await page.reload();

		await page.waitForSelector( '.wp-block-amp-amp-story-post-date' );
		await selectBlockByClassName( 'wp-block-amp-amp-story-post-date' );

		page.waitForSelector( '.components-animate__appear button[aria-label="Begin after: Story Author"]' );

		expect( await page.evaluate( () => {
			return document.querySelector( '.components-animate__appear button[aria-label="Begin after: Story Author"]' ).innerHTML;
		} ) ).toContain( 'admin' );
	} );

	it( 'should not allow creating a cycle in animation after', async () => {
		// Add Author block with animation.
		await insertBlock( 'Author' );
		await page.select( '.components-select-control__input', 'fly-in-bottom' );

		// Add Date block with animation.
		await insertBlock( 'Date' );
		await page.select( '.components-select-control__input', 'pulse' );
		await page.waitForSelector( 'label[for="amp-stories-animation-order-picker"]' );
		await clickButton( 'Immediately' );

		await page.waitForSelector( '.components-animate__appear button[aria-checked="false"]' );
		await page.click( '.components-animate__appear button[aria-checked="false"]' );

		await selectBlockByClassName( 'wp-block-amp-amp-story-post-author' );

		await page.waitForSelector( 'label[for="amp-stories-animation-order-picker"]' );
		await clickButton( 'Immediately' );
		await page.waitForSelector( '.components-animate__appear' );

		expect( await page.evaluate( () => {
			return document.querySelector( '.components-animate__appear button[aria-checked="false"]' );
		} ) ).toBeNull();
	} );
} );
