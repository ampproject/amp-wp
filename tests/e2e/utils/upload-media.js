/**
 * External dependencies
 */
import path from 'path';
import fs from 'fs';
import os from 'os';
import { v4 as uuidv4 } from 'uuid';

/**
 * Uploads a file to the Media Library, and awaits its upload.
 *
 * The file should be in tests/e2e/assets/,
 * though the file argument should only have the name, not the directory.
 * For example, 'foo-baz.mp4'.
 *
 * @param {string|null} file The file name to upload, not including the directory.
 * @return {string|null} The name of the file as it was uploaded, also not including the full path.
 */
export async function uploadMedia( file ) {
	const fileExtensionMatches = file.match( /\.\w+$/ );
	if ( ! fileExtensionMatches.hasOwnProperty( 0 ) ) {
		return null;
	}
	const fileExtension = fileExtensionMatches[ 0 ];

	// Wait for media modal to appear and upload video.
	await page.waitForSelector( '.media-modal input[type=file]' );
	const inputElement = await page.$( '.media-modal input[type=file]' );
	const testMediaPath = path.join( __dirname, '..', 'assets', file );
	const filename = uuidv4();

	const fileWithExtension = filename + fileExtension;
	const tmpFileName = path.join( os.tmpdir(), fileWithExtension );
	fs.copyFileSync( testMediaPath, tmpFileName );
	await inputElement.uploadFile( tmpFileName );

	// Wait for upload.
	await page.waitForSelector( `.media-modal li[aria-label="${ filename }"]` );

	return fileWithExtension;
}
