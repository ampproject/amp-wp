/**
 * WordPress dependencies
 */
import { createNewPost } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, insertBlock, uploadMedia, openPreviewPage } from '../../utils';

describe( 'Video block', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	/**
	 * This test is disabled because it does not work on Chromium.
	 *
	 * @see https://github.com/ampproject/amp-wp/pull/2874
	 * @see https://github.com/ampproject/amp-wp/pull/3205
	 */
	// eslint-disable-next-line jest/no-disabled-tests
	it.skip( 'should allow changing the ARIA label for the video block', async () => {
		await createNewPost( { postType: 'amp_story' } );

		// Using the regular inserter.
		await insertBlock( 'Video' );

		// Click the media library button.
		await page.waitForSelector( '.editor-media-placeholder__media-library-button' );
		await page.click( '.editor-media-placeholder__media-library-button' );
		await uploadMedia( 'clothes-hanged-to-dry-1295231.mp4' );

		// Insert the uploaded video.
		await page.click( '.media-modal button.media-button-select' );

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
} );
