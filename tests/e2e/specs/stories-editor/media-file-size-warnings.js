/**
 * WordPress dependencies
 */
import { createNewPost, getAllBlocks, selectBlockByClientId } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, insertBlock, uploadMedia } from '../../utils';

const EXPECTED_NOTICE_TEXT = 'A video size of less than 1 MB per second is recommended. The selected video is 2 MB per second.';
const NOTICE_SELECTOR = '.media-toolbar-secondary .notice-warning';
const OVERSIZED_VIDEO = 'oversize-video-45321.mp4';
const CORRECT_VIDEO = 'clothes-hanged-to-dry-1295231.mp4';
const SELECT_BUTTON = '.media-modal button.media-button-select';

/**
 * Tests the notices for excessive video size.
 *
 * These are similar to the tests in video-poster-image-extraction.test.js.
 */
describe( 'Media File Size Warnings', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	describe( 'Background Media', () => {
		it( 'should display a warning in the Media Library when a large video is uploaded', async () => {
			await createNewPost( { postType: 'amp_story' } );

			// Select the default page block.
			await selectBlockByClientId(
				( await getAllBlocks() )[ 0 ].clientId
			);

			// Click the media selection button.
			await page.waitForSelector( '.editor-amp-story-page-background' );
			await page.click( '.editor-amp-story-page-background' );
			await uploadMedia( OVERSIZED_VIDEO );

			// The warning notice text should appear.
			const warningNotice = await page.$( NOTICE_SELECTOR );
			await expect( warningNotice ).toMatch( EXPECTED_NOTICE_TEXT );

			// It should be possible to insert the uploaded video, as this should not disable the 'Select' button.
			await expect( page ).toClick( SELECT_BUTTON );
		} );

		it( 'should not display a warning in the Media Library for an appropriate video file size', async () => {
			await createNewPost( { postType: 'amp_story' } );

			// Select the default page block.
			await selectBlockByClientId(
				( await getAllBlocks() )[ 0 ].clientId
			);

			// Click the media selection button.
			await page.waitForSelector( '.editor-amp-story-page-background' );
			await page.click( '.editor-amp-story-page-background' );
			await uploadMedia( CORRECT_VIDEO );

			// The warning Notice component should not appear.
			await expect( page ).not.toMatchElement( NOTICE_SELECTOR );

			// The warning notice text should not appear.
			await expect( page ).not.toMatch( EXPECTED_NOTICE_TEXT );

			// It should be possible to insert the uploaded video.
			await expect( page ).toClick( SELECT_BUTTON );
		} );
	} );

	describe( 'Video Block', () => {
		it( 'should display a warning in the Media Library when a large video is uploaded', async () => {
			await createNewPost( { postType: 'amp_story' } );

			// Using the regular inserter.
			await insertBlock( 'Video' );

			// Click the media library button.
			await page.waitForSelector( '.editor-media-placeholder__media-library-button' );
			await page.click( '.editor-media-placeholder__media-library-button' );
			await uploadMedia( OVERSIZED_VIDEO );

			// The warning notice text should appear.
			const warningNotice = await page.$( NOTICE_SELECTOR );
			await expect( warningNotice ).toMatch( EXPECTED_NOTICE_TEXT );

			// It should be possible to insert the uploaded video, as this should not disable the 'Select' button.
			await expect( page ).toClick( SELECT_BUTTON );
		} );

		it( 'should not display a warning in the Media Library for a correct video file size', async () => {
			await createNewPost( { postType: 'amp_story' } );

			// Using the regular inserter.
			await insertBlock( 'Video' );

			// Click the media library button.
			await page.waitForSelector( '.editor-media-placeholder__media-library-button' );
			await page.click( '.editor-media-placeholder__media-library-button' );
			await uploadMedia( CORRECT_VIDEO );

			// The warning Notice component should not appear.
			await expect( page ).not.toMatchElement( NOTICE_SELECTOR );

			// The warning notice text should not appear.
			await expect( page ).not.toMatch( EXPECTED_NOTICE_TEXT );

			// It should be possible to insert the uploaded video.
			await expect( page ).toClick( SELECT_BUTTON );
		} );
	} );
} );
