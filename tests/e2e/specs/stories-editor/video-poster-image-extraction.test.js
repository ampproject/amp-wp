/**
 * External dependencies
 */
import path from 'path';
import fs from 'fs';
import os from 'os';
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { createNewPost, getAllBlocks, selectBlockByClientId } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience } from '../../utils';

describe( 'Video Poster Image Extraction', () => {
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

			// Wait for media modal to appear and upload video.
			await page.waitForSelector( '.media-modal input[type=file]' );
			const inputElement = await page.$( '.media-modal input[type=file]' );
			const testImagePath = path.join( __dirname, '..', '..', 'assets', 'clothes-hanged-to-dry-1295231.mp4' );
			const filename = uuid();
			const tmpFileName = path.join( os.tmpdir(), filename + '.mp4' );
			fs.copyFileSync( testImagePath, tmpFileName );
			await inputElement.uploadFile( tmpFileName );

			// Wait for upload.
			await page.waitForSelector( `.media-modal li[aria-label="${ filename }"]` );

			// Insert the uploaded video.
			await page.click( '.media-modal button.media-button-select' );

			// Wait for video to be inserted.
			await page.waitForSelector( '.editor-amp-story-page-video' );

			// Wait for poster to be extracted.
			await page.waitForSelector( '#editor-amp-story-page-poster' );
		} );
	} );
} );
