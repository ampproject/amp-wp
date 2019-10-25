/**
 * WordPress dependencies
 */
import {
	createNewPost,
	saveDraft,
	selectBlockByClientId,
	getAllBlocks,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	deactivateExperience,
	clickButton,
	clickButtonByLabel,
	uploadMedia,
	openPreviewPage,
	insertBlock,
	setStorySettings,
} from '../../utils';

const LARGE_IMAGE = 'large-image-36521.jpg';
const CORRECT_VIDEO = 'clothes-hanged-to-dry-1295231.mp4';
const SELECT_BUTTON = '.media-modal button.media-button-select';

const DOCUMENT_PANEL = 'button.edit-post-sidebar__panel-tab[data-label="Document"]';
const SETTINGS_PANEL = '.amp-story-settings-panel';
const SETTINGS_ADVANCE_AFTER = '.amp-story-settings-advance-after select';
const SETTINGS_ADVANCE_AFTER_DURATION = '.amp-story-settings-advance-after-duration .components-range-control__number';

/**
 * Helper to retrieve a current value from form fields.
 *
 * @param {string} inputSelector Selector that matches an element with a value attribute.
 */
async function getInputValue( inputSelector ) {
	const value = await page.evaluate( ( selector ) => {
		return document.querySelector( selector ).value;
	}, inputSelector );

	return value;
}

/**
 * Helper to clear form fields' value.
 *
 * @param {string} inputSelector Selector that matches an element with a value attribute.
 */
async function clearInputValue( inputSelector ) {
	await page.evaluate( ( selector ) => {
		document.querySelector( selector ).value = '';
	}, inputSelector );
}

/**
 * Expands the Story settings panel.
 */
async function expandSettingsPanel() {
	await page.click( DOCUMENT_PANEL );
	await page.waitForSelector( SETTINGS_PANEL );
	await page.click( SETTINGS_PANEL );
}

describe( 'Story Page', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
		await setStorySettings( 'time', '2' );
	} );

	afterAll( async () => {
		await setStorySettings( '', '0' );
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
		await clickButtonByLabel( 'Color: Vivid red' );

		await saveDraft();
		await page.reload();

		const style = 'background-color: rgb(207, 46, 46); opacity: 1;';

		const nodes = await page.$x(
			`//div[contains(@class, "amp-page-active")]//div[contains(@style,"${ style }")]`
		);
		expect( nodes ).not.toHaveLength( 0 );
	} );

	it( 'should allow adding gradient', async () => {
		await clickButtonByLabel( 'Color: Vivid red' );
		await clickButton( 'Add Gradient' );

		await saveDraft();
		await page.reload();

		const style = 'background-image: linear-gradient(rgb(207, 46, 46), transparent)';

		const nodes = await page.$x(
			`//div[contains(@class, "amp-page-active")]//div[contains(@style,"${ style }")]`
		);
		expect( nodes ).not.toHaveLength( 0 );
	} );

	it( 'should allow adding opacity', async () => {
		await clickButtonByLabel( 'Color: Vivid red' );

		const opacitySelector = '.components-range-control__number[aria-label="Opacity"]';
		await page.waitForSelector( opacitySelector );

		// Set opacity to 15.
		await clearInputValue( opacitySelector );
		await page.type( opacitySelector, '15' );

		await saveDraft();
		await page.reload();

		const style = 'opacity: 0.15;';
		const nodes = await page.$x(
			`//div[contains(@class, "amp-page-active")]//div[contains(@style,"${ style }")]`
		);
		expect( nodes ).not.toHaveLength( 0 );
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

	it( 'should save the page advancement setting correctly', async () => {
		const pageAdvancementSelector = '.components-select-control__input';
		await page.waitForSelector( pageAdvancementSelector );
		await page.select( pageAdvancementSelector, 'time' );

		const secondsSelector = 'input[aria-label="Time in seconds"]';
		await page.waitForSelector( secondsSelector );

		await clearInputValue( secondsSelector );
		await page.type( secondsSelector, '5' );

		await saveDraft();
		await page.reload();

		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);
		await page.waitForSelector( secondsSelector );

		expect( await getInputValue( secondsSelector ) ).toBe( '5' );

		const editorPage = page;
		const previewPage = await openPreviewPage( editorPage, 'amp-story' );
		await previewPage.waitForSelector( '.amp-story-block-wrapper' );

		const [ elementHandle ] = await previewPage.$x( '//amp-story-page/@auto-advance-after' );
		const secondsHandle = await elementHandle.getProperty( 'value' );
		const seconds = await secondsHandle.jsonValue();
		expect( seconds ).toStrictEqual( '5s' );
	} );

	it( 'should consider animations time when setting the page advancement', async () => {
		await insertBlock( 'Author' );

		const animationTypeSelector = '.is-opened select.components-select-control__input';
		await page.waitForSelector( animationTypeSelector );
		await page.select( animationTypeSelector, 'pulse' );

		const animationDelaySelector = 'input[aria-label="Delay (ms)"]';
		await page.waitForSelector( animationDelaySelector );
		await page.type( animationDelaySelector, '3500' );

		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);

		const pageAdvancementSelector = '.components-select-control__input';
		await page.waitForSelector( pageAdvancementSelector );
		await page.select( pageAdvancementSelector, 'time' );

		const secondsSelector = 'input[aria-label="Time in seconds"]';
		await page.waitForSelector( secondsSelector );

		expect( await getInputValue( secondsSelector ) ).toBe( '4' );
	} );

	it( 'should allow changing the alt attribute for the background image', async () => {
		// Add background image.
		await page.waitForSelector( '.editor-amp-story-page-background' );
		await page.click( '.editor-amp-story-page-background' );
		await uploadMedia( LARGE_IMAGE );
		await expect( page ).toClick( SELECT_BUTTON );

		// Write assistive text.
		const label = await page.waitForXPath( `//label[contains(text(), 'Assistive Text')]` );
		await page.evaluate( ( lbl ) => {
			lbl.click();
		}, label );
		await page.keyboard.type( 'Hello World' );

		// Open preview.
		const editorPage = page;
		const previewPage = await openPreviewPage( editorPage, 'amp-story' );
		await previewPage.waitForSelector( 'amp-img[alt*="Hello World"]' );
	} );

	/**
	 * This test is disabled because it does not work on Chromium.
	 *
	 * @see https://github.com/ampproject/amp-wp/pull/2874
	 * @see https://github.com/ampproject/amp-wp/pull/3214
	 */
	// eslint-disable-next-line jest/no-disabled-tests
	it.skip( 'should allow changing the ARIA label for the background video', async () => {
		// Add background video.
		await page.waitForSelector( '.editor-amp-story-page-background' );
		await page.click( '.editor-amp-story-page-background' );
		await uploadMedia( CORRECT_VIDEO );
		await page.click( SELECT_BUTTON );

		// Write assistive text.
		const label = await page.waitForXPath( `//label[contains(text(), 'Assistive Text')]` );
		await page.evaluate( ( lbl ) => {
			lbl.click();
		}, label );
		await page.keyboard.type( 'Hello World' );

		// Open preview.
		const editorPage = page;
		const previewPage = await openPreviewPage( editorPage, 'amp-story' );
		expect( await previewPage.$x( '//amp-video[contains(@aria-label, "Hello World")]' ) ).toHaveLength( 1 );
	} );

	it( 'should pass global story settings to a new story', async () => {
		await expandSettingsPanel();
		await page.waitForSelector( SETTINGS_ADVANCE_AFTER );

		expect( await getInputValue( SETTINGS_ADVANCE_AFTER ) ).toBe( 'time' );
		expect( await getInputValue( SETTINGS_ADVANCE_AFTER_DURATION ) ).toBe( '2' );
	} );

	it( 'should save the story settings correctly', async () => {
		await expandSettingsPanel();
		await page.waitForSelector( SETTINGS_ADVANCE_AFTER );

		await page.select( SETTINGS_ADVANCE_AFTER, 'auto' );

		await saveDraft();
		await page.reload();

		await expandSettingsPanel();
		await page.waitForSelector( SETTINGS_ADVANCE_AFTER );

		expect( await getInputValue( SETTINGS_ADVANCE_AFTER ) ).toBe( 'auto' );
	} );

	it( 'should apply story settings to newly created pages', async () => {
		await insertBlock( 'Page' );

		const pageAdvancementSelector = '.components-select-control__input';
		await page.waitForSelector( pageAdvancementSelector );

		const secondsSelector = 'input[aria-label="Time in seconds"]';
		await page.waitForSelector( secondsSelector );

		expect( await getInputValue( pageAdvancementSelector ) ).toBe( 'time' );
		expect( await getInputValue( secondsSelector ) ).toBe( '2' );
	} );

	it( 'should not affect story settings when individual page settings are changed', async () => {
		await insertBlock( 'Page' );

		const pageAdvancementSelector = '.components-select-control__input';
		await page.waitForSelector( pageAdvancementSelector );

		const secondsSelector = 'input[aria-label="Time in seconds"]';
		await page.waitForSelector( secondsSelector );

		await clearInputValue( secondsSelector );
		await page.type( secondsSelector, '5' );

		await expandSettingsPanel();

		expect( await getInputValue( SETTINGS_ADVANCE_AFTER_DURATION ) ).toBe( '2' );
	} );

	it( 'should not affect existing stories when story defaults are changed', async () => {
		await saveDraft();
		await page.reload();

		await expandSettingsPanel();
		await page.waitForSelector( SETTINGS_ADVANCE_AFTER );

		const originalStoryAdvancement = await getInputValue( SETTINGS_ADVANCE_AFTER );
		const originalStoryAdvancementDuration = await getInputValue( SETTINGS_ADVANCE_AFTER_DURATION );

		expect( originalStoryAdvancement ).toBe( 'time' );
		expect( originalStoryAdvancementDuration ).toBe( '2' );

		// Save the first post admin URL for later.
		const originalPostAdminUrl = page.url();

		await setStorySettings( 'time', '10' );

		// Create a second post with new defaults.
		await createNewPost( { postType: 'amp_story' } );
		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);

		await saveDraft();
		await page.reload();

		await expandSettingsPanel();
		await page.waitForSelector( SETTINGS_ADVANCE_AFTER );

		const newStoryAdvancement = await getInputValue( SETTINGS_ADVANCE_AFTER );
		const newStoryAdvancementDuration = await getInputValue( SETTINGS_ADVANCE_AFTER_DURATION );

		expect( newStoryAdvancement ).toBe( 'time' );
		expect( newStoryAdvancementDuration ).toBe( '10' );

		// Return back to the original post with the original settings.
		await page.goto( originalPostAdminUrl );

		const compareOriginalStoryAdvancement = await getInputValue( SETTINGS_ADVANCE_AFTER );
		const compareOriginalStoryAdvancementDuration = await getInputValue( SETTINGS_ADVANCE_AFTER_DURATION );

		expect( originalStoryAdvancement ).toStrictEqual( compareOriginalStoryAdvancement );
		expect( originalStoryAdvancementDuration ).toStrictEqual( compareOriginalStoryAdvancementDuration );

		// Change back to defaults
		await setStorySettings( 'time', '2' );
	} );
} );
