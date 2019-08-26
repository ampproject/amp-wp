/**
 * WordPress dependencies
 */
import { createNewPost, getAllBlocks, selectBlockByClientId } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, clickButton, deactivateExperience, insertBlock, uploadMedia } from '../../utils';

const FEATURED_IMAGE_NOTICE_TEXT = 'Selecting a featured image is required.';
const LARGE_IMAGE = 'large-image-36521.jpg';
const MEDIA_LIBRARY_BUTTON = '.editor-media-placeholder__media-library-button';
const SELECT_BUTTON = '.media-modal button.media-button-select';

const getFeaturedImageFromDocument = () => page.evaluate( () => (
	document.querySelector( '.editor-post-featured-image img' ).getAttribute( 'src' )
) );

const getFeaturedImageFromStore = () => page.evaluate( () => (
	wp.data.select( 'core/editor' ).getEditedPostAttribute( 'featured_media' )
) );

/**
 * Tests that the featured image is automatically set when selecting an image in certain places.
 *
 * These are similar to the tests in video-poster-image-extraction.test.js.
 */
describe( 'Featured Image Automatically Set', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	describe( 'Image Block', () => {
		it( 'should set the featured image on uploading a big enough image to the Core Image block', async () => {
			await createNewPost( { postType: 'amp_story' } );

			await insertBlock( 'Image' );

			// Click the media library button.
			await page.waitForSelector( MEDIA_LIBRARY_BUTTON );

			await page.click( MEDIA_LIBRARY_BUTTON );
			const uploadedImage = await uploadMedia( LARGE_IMAGE );

			// Select the image from the Media Library.
			await page.waitForSelector( SELECT_BUTTON );
			await page.click( SELECT_BUTTON );

			// Wait for image to appear in the block.
			await page.waitForSelector( '.wp-block img' );

			await clickButton( 'Document' );
			await clickButton( 'Featured Image' );

			// The featured image warning notice text should not appear.
			await expect( page ).not.toMatch( FEATURED_IMAGE_NOTICE_TEXT );

			// The featured image on the page should be set as the image that was uploaded to the Image block.
			expect( await getFeaturedImageFromDocument() ).toContain( uploadedImage );

			// The featured image in the store should not be 0, meaning it is set.
			expect( await getFeaturedImageFromStore() ).not.toStrictEqual( 0 );
		} );
	} );

	describe( 'Background Media', () => {
		it( 'should set the featured image on uploading a big enough image as the Background Media', async () => {
			await createNewPost( { postType: 'amp_story' } );

			// Select the default page block.
			await selectBlockByClientId(
				( await getAllBlocks() )[ 0 ].clientId
			);

			// Click the media selection button.
			await page.waitForSelector( '.editor-amp-story-page-background' );
			await page.click( '.editor-amp-story-page-background' );
			const uploadedImage = await uploadMedia( LARGE_IMAGE );

			// Select the image from the Media Library.
			await page.waitForSelector( SELECT_BUTTON );
			await page.click( SELECT_BUTTON );

			await clickButton( 'Document' );
			await clickButton( 'Featured Image' );

			// The featured image warning notice text should not appear.
			await expect( page ).not.toMatch( FEATURED_IMAGE_NOTICE_TEXT );

			// The featured image on the page should be set as the image that was uploaded as the 'Background Media'.
			expect( await getFeaturedImageFromDocument() ).toContain( uploadedImage );

			// The featured image in the store should not be 0, meaning it is set.
			expect( await getFeaturedImageFromStore() ).not.toStrictEqual( 0 );
		} );
	} );
} );
