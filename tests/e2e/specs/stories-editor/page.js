/**
 * WordPress dependencies
 */
import { createNewPost, saveDraft, selectBlockByClientId, getAllBlocks } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	deactivateExperience,
	clickButton,
	clickButtonByLabel,
	getSidebarPanelToggleByTitle,
	uploadMedia,
	openPreviewPage,
	insertBlock,
} from '../../utils';

describe( 'Story Page', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
		// Select the default page block.
		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);
	} );

	it( 'should be possible to add background color to Page', async () => {
		const panelTitle = 'Background Color';
		const colorToggle = await getSidebarPanelToggleByTitle( panelTitle );

		await colorToggle.click();

		await clickButtonByLabel( 'Color: Vivid red' );

		await saveDraft();
		await page.reload();

		const style = 'background-color: rgb(207, 46, 46); opacity: 1;';

		const nodes = await page.$x(
			`//div[contains(@class, "amp-page-active")]//div[contains(@style,"${ style }")]`
		);
		expect( nodes.length ).not.toStrictEqual( 0 );
	} );

	it( 'should allow adding gradient', async () => {
		const panelTitle = 'Background Color';
		const colorToggle = await getSidebarPanelToggleByTitle( panelTitle );

		await colorToggle.click();
		await clickButtonByLabel( 'Color: Vivid red' );
		await clickButton( 'Add Gradient' );

		await saveDraft();
		await page.reload();

		const style = 'background-image: linear-gradient(rgb(207, 46, 46), transparent)';

		const nodes = await page.$x(
			`//div[contains(@class, "amp-page-active")]//div[contains(@style,"${ style }")]`
		);
		expect( nodes.length ).not.toStrictEqual( 0 );
	} );

	it( 'should allow adding opacity', async () => {
		const panelTitle = 'Background Color';
		const colorToggle = await getSidebarPanelToggleByTitle( panelTitle );

		await colorToggle.click();
		const opacitySelector = '.components-range-control__number[aria-label="Opacity"]';
		await page.waitForSelector( opacitySelector );

		// Set opacity to 15.
		await page.evaluate( () => {
			document.querySelector( '.components-range-control__number[aria-label="Opacity"]' ).value = '';
		} );

		await page.type( opacitySelector, '15' );

		await clickButtonByLabel( 'Color: Vivid red' );

		await saveDraft();
		await page.reload();

		const style = 'opacity: 0.15;';
		const nodes = await page.$x(
			`//div[contains(@class, "amp-page-active")]//div[contains(@style,"${ style }")]`
		);
		expect( nodes.length ).not.toStrictEqual( 0 );
	} );

	it( 'should be possible to add Background Image', async () => {
		// Click the media selection button.
		await page.waitForSelector( '.editor-amp-story-page-background' );
		await page.click( '.editor-amp-story-page-background' );
		await uploadMedia( 'large-image-36521.jpg' );

		// Insert the image.
		await page.click( '.media-modal button.media-button-select' );

		// Wait for media to be inserted.
		await page.waitForSelector( '.components-focal-point-picker-wrapper' );
		await saveDraft();
		await page.reload();

		const editorPage = page;
		const previewPage = await openPreviewPage( editorPage, 'amp-story' );
		await previewPage.waitForSelector( '.amp-story-block-wrapper' );

		const [ elementHandle ] = await previewPage.$x( '//amp-story-page/amp-story-grid-layer/amp-img/@src' );
		const srcHandle = await elementHandle.getProperty( 'value' );
		const src = await srcHandle.jsonValue();
		expect( src ).toContain( '.jpg' );
		expect( src ).toContain( 'wp-content/uploads' );
	} );

	it( 'should save tha page advancement setting correctly', async () => {
		const pageAdvancementSelector = '.components-select-control__input';
		await page.waitForSelector( pageAdvancementSelector );
		await page.select( pageAdvancementSelector, 'time' );

		const secondsSelector = 'input[aria-label="Time in seconds"]';
		await page.waitForSelector( secondsSelector );
		await page.type( secondsSelector, '15' );

		await saveDraft();
		await page.reload();

		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);
		await page.waitForSelector( secondsSelector );

		expect( await page.evaluate( ( selector ) => {
			return document.querySelector( selector ).value;
		}, secondsSelector ) ).toBe( '15' );

		const editorPage = page;
		const previewPage = await openPreviewPage( editorPage, 'amp-story' );
		await previewPage.waitForSelector( '.amp-story-block-wrapper' );

		const [ elementHandle ] = await previewPage.$x( '//amp-story-page/@auto-advance-after' );
		const secondsHandle = await elementHandle.getProperty( 'value' );
		const seconds = await secondsHandle.jsonValue();
		expect( seconds ).toStrictEqual( '15s' );
	} );

	it( 'should consider animations time when setting the page advancement', async () => {
		await insertBlock( 'Author' );

		const animationTypeSelector = '.is-opened select.components-select-control__input';
		await page.waitForSelector( animationTypeSelector );
		await page.select( animationTypeSelector, 'pulse' );

		const animationDelaySelector = 'input[aria-label="Delay (ms)"]';
		await page.waitForSelector( animationDelaySelector );
		await page.type( animationDelaySelector, '1500' );

		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);

		const pageAdvancementSelector = '.components-select-control__input';
		await page.waitForSelector( pageAdvancementSelector );
		await page.select( pageAdvancementSelector, 'time' );

		const secondsSelector = 'input[aria-label="Time in seconds"]';
		await page.waitForSelector( secondsSelector );

		expect( await page.evaluate( ( selector ) => {
			return document.querySelector( selector ).value;
		}, secondsSelector ) ).toBe( '2' );
	} );
} );
