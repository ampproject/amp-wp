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

	const animationTypeSelector = '.components-select-control__input';
	const unSelectedAnimationSelector = '.components-animate__appear button[aria-checked="false"]';
	const authorBlockClassName = 'wp-block-amp-amp-story-post-author';

	it( 'should save correct animation values', async () => {
		// Add Author block with animation.

		const animationDelaySelector = 'input[aria-label="Delay (ms)"]';
		await insertBlock( 'Author' );
		await page.waitForSelector( animationTypeSelector );
		await page.select( animationTypeSelector, 'fly-in-bottom' );
		await page.waitForSelector( animationDelaySelector );
		await page.type( animationDelaySelector, '15' );

		await saveDraft();
		await page.reload();

		await page.waitForSelector( `.${ authorBlockClassName }` );
		await selectBlockByClassName( authorBlockClassName );
		await page.waitForSelector( animationDelaySelector );

		expect( await page.evaluate( ( selector ) => {
			return document.querySelector( selector ).value;
		}, animationDelaySelector ) ).toBe( '15' );

		expect( await page.evaluate( () => {
			return document.querySelector( '.components-select-control__input [value="fly-in-bottom"]' ).selected;
		} ) ).toBe( true );
	} );

	it( 'should save correct animation after values', async () => {
		// Add Author block with animation.
		await insertBlock( 'Author' );
		await page.waitForSelector( animationTypeSelector );
		await page.select( animationTypeSelector, 'fly-in-bottom' );

		// Add Date block with animation.
		await insertBlock( 'Date' );
		await page.waitForSelector( animationTypeSelector );
		await page.select( animationTypeSelector, 'pulse' );

		// Add Author block as Begin After.
		await page.waitForSelector( 'button[aria-label="Begin immediately"]' );
		await clickButton( 'Immediately' );

		await page.waitForSelector( unSelectedAnimationSelector );
		await page.evaluate( ( selector ) => {
			document.querySelector( selector ).click();
		}, unSelectedAnimationSelector );
		await saveDraft();
		await page.reload();

		const dateBlockClassName = 'wp-block-amp-amp-story-post-date';
		await page.waitForSelector( `.${ dateBlockClassName }` );
		await selectBlockByClassName( dateBlockClassName );

		const selectedAuthorSelector = 'button[aria-label="Begin after: Story Author"]';
		await page.waitForSelector( selectedAuthorSelector );

		expect( await page.evaluate( ( selector ) => {
			return document.querySelector( selector ).innerHTML;
		}, selectedAuthorSelector ) ).toContain( 'admin' );
	} );

	it( 'should not allow creating a cycle in animation after', async () => {
		// Add Author block with animation.
		await insertBlock( 'Author' );
		await page.select( animationTypeSelector, 'fly-in-bottom' );

		// Add Date block with animation.
		await insertBlock( 'Date' );
		await page.select( animationTypeSelector, 'pulse' );
		await page.waitForSelector( 'label[for="amp-stories-animation-order-picker"]' );
		await clickButton( 'Immediately' );

		await page.waitForSelector( unSelectedAnimationSelector );
		await page.evaluate( ( selector ) => {
			document.querySelector( selector ).click();
		}, unSelectedAnimationSelector );
		await selectBlockByClassName( authorBlockClassName );

		await page.waitForSelector( 'label[for="amp-stories-animation-order-picker"]' );
		await clickButton( 'Immediately' );
		await page.waitForSelector( '.components-animate__appear' );

		expect( await page.evaluate( ( selector ) => {
			return document.querySelector( selector );
		}, unSelectedAnimationSelector ) ).toBeNull();

		expect( await page.evaluate( () => {
			return document.querySelector( 'button[aria-label="Begin immediately"]' ).innerHTML;
		} ) ).toContain( 'Immediately' );
	} );
} );
