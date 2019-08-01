/**
 * External dependencies
 */
import path from 'path';
import fs from 'fs';
import os from 'os';
import uuid from 'uuid/v4';

/**
 * Uploads a file to the Media Library, and awaits its upload.
 *
 * The file should be in tests/e2e/assets/,
 * though the file argument should only have the name, not the directory.
 * For example, 'foo-baz.mp4'.
 *
 * @param {string} file The file name to upload, not including the directory.
 */
export async function uploadMedia( file ) {
	// Wait for media modal to appear and upload video.
	await page.waitForSelector( '.media-modal input[type=file]' );
	const inputElement = await page.$( '.media-modal input[type=file]' );
	const testImagePath = path.join( __dirname, '..', 'assets', file );
	const filename = uuid();
	const tmpFileName = path.join( os.tmpdir(), filename + '.mp4' );
	fs.copyFileSync( testImagePath, tmpFileName );
	await inputElement.uploadFile( tmpFileName );

	// Wait for upload.
	await page.waitForSelector( `.media-modal li[aria-label="${ filename }"]` );
}
