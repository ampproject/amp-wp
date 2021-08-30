/**
 * WordPress dependencies
 */
import {
	createNewPost,
	ensureSidebarOpened,
	insertBlock,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	clickButton,
	getBlockEditorSidebarToggle,
	uploadMedia,
} from '../../utils';

const sampleVideo = 'sample-video.mp4';
const autoplayNotice = 'Autoplay may cause usability issues for some users.';
const mutedNotice = 'Autoplay will mute the video player by default in AMP mode.';

/**
 * Tests the notices for the featured image.
 */
describe( 'Video Block Muted Notice', () => {
	beforeEach( async () => {
		await createNewPost( { postType: 'post' } );
	} );

	it( 'displays a message only if the autoplay is turned on and the muted option is off', async () => {
		await insertBlock( 'Video' );
		await page.waitForSelector( '.wp-block-video .block-editor-media-placeholder' );
		await clickButton( 'Media Library' );
		await uploadMedia( sampleVideo );
		await page.waitForSelector( 'button.media-button-select:not([disabled])' );
		await expect( page ).toClick( 'button.media-button-select:not([disabled])' );

		let [ autoplayContainer, autoplayInput ] = await getBlockEditorSidebarToggle( 'Autoplay' );
		let [ mutedContainer, mutedInput ] = await getBlockEditorSidebarToggle( 'Muted' );

		// Confirm both autoplay and muted are off.
		await expect( await autoplayInput.evaluate( ( node ) => node.checked ) ).toBe( false );
		await expect( await mutedInput.evaluate( ( node ) => node.checked ) ).toBe( false );
		await expect( await autoplayContainer.evaluate( ( node ) => node.textContent ) ).not.toMatch( autoplayNotice );
		await expect( await mutedContainer.evaluate( ( node ) => node.textContent ) ).not.toMatch( mutedNotice );

		await autoplayInput.click();

		// Check if both notices are displayed after turning autoplay on.
		await expect( await autoplayInput.evaluate( ( node ) => node.checked ) ).toBe( true );
		await expect( await mutedInput.evaluate( ( node ) => node.checked ) ).toBe( false );
		await expect( await autoplayContainer.evaluate( ( node ) => node.textContent ) ).toMatch( autoplayNotice );
		await expect( await mutedContainer.evaluate( ( node ) => node.textContent ) ).toMatch( mutedNotice );

		// Insert new block so that the sidebar content changes.
		await insertBlock( 'Code' );
		await page.waitForSelector( '.wp-block-code' );
		await ensureSidebarOpened();

		// Switch back to the video block and confirm messages have been persisted.
		await expect( page ).toClick( '.wp-block-video' );

		[ autoplayContainer, autoplayInput ] = await getBlockEditorSidebarToggle( 'Autoplay' );
		[ mutedContainer, mutedInput ] = await getBlockEditorSidebarToggle( 'Muted' );

		await expect( await autoplayInput.evaluate( ( node ) => node.checked ) ).toBe( true );
		await expect( await mutedInput.evaluate( ( node ) => node.checked ) ).toBe( false );
		await expect( await autoplayContainer.evaluate( ( node ) => node.textContent ) ).toMatch( autoplayNotice );
		await expect( await mutedContainer.evaluate( ( node ) => node.textContent ) ).toMatch( mutedNotice );

		await mutedInput.click();

		await expect( await autoplayInput.evaluate( ( node ) => node.checked ) ).toBe( true );
		await expect( await mutedInput.evaluate( ( node ) => node.checked ) ).toBe( true );
		await expect( await autoplayContainer.evaluate( ( node ) => node.textContent ) ).toMatch( autoplayNotice );
		await expect( await mutedContainer.evaluate( ( node ) => node.textContent ) ).not.toMatch( mutedNotice );

		await autoplayInput.click();

		await expect( await autoplayInput.evaluate( ( node ) => node.checked ) ).toBe( false );
		await expect( await mutedInput.evaluate( ( node ) => node.checked ) ).toBe( true );
		await expect( await autoplayContainer.evaluate( ( node ) => node.textContent ) ).not.toMatch( autoplayNotice );
		await expect( await mutedContainer.evaluate( ( node ) => node.textContent ) ).not.toMatch( mutedNotice );
	} );
} );
