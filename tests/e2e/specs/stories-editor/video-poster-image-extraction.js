/**
 * WordPress dependencies
 */
import { createNewPost, getAllBlocks, selectBlockByClientId } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, insertBlock, uploadMedia } from '../../utils';

/**
 * These tests are disabled because they do not work on Chromium.
 *
 * @see https://github.com/ampproject/amp-wp/pull/2874
 */
describe.skip( 'Video Poster Image Extraction', () => { // eslint-disable-line jest/no-disabled-tests
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	describe( 'Page block', () => {
		it( 'should extract the poster image from a newly uploaded background video', async () => {
			await createNewPost( { postType: 'amp_story' } );

			// Select the default page block.
			await selectBlockByClientId(
				( await getAllBlocks() )[ 0 ].clientId
			);

			// Click the media selection button.
			await page.waitForSelector( '.editor-amp-story-page-background' );
			await page.click( '.editor-amp-story-page-background' );
			await uploadMedia( 'clothes-hanged-to-dry-1295231.mp4' );

			// Insert the uploaded video.
			await page.click( '.media-modal button.media-button-select' );

			// Wait for video to be inserted.
			await page.waitForSelector( '.editor-amp-story-page-video' );

			// Wait for poster to be extracted.
			await expect( page ).toMatchElement( '#editor-amp-story-page-poster' );
		} );
	} );

	describe( 'Video block', () => {
		it( 'should extract the poster image from a newly added video', async () => {
			await createNewPost( { postType: 'amp_story' } );

			// Using the regular inserter.
			await insertBlock( 'Video' );

			// Click the media library button.
			await page.waitForSelector( '.editor-media-placeholder__media-library-button' );
			await page.click( '.editor-media-placeholder__media-library-button' );
			await uploadMedia( 'clothes-hanged-to-dry-1295231.mp4' );

			// Insert the uploaded video.
			await page.click( '.media-modal button.media-button-select' );

			// Wait for video to be inserted.
			await page.waitForSelector( '.wp-block-video video' );

			// Wait for poster to be extracted.
			await expect( page ).toMatchElement( '.video-block__poster-image img' );
		} );
	} );
} );
