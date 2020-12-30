/**
 * WordPress dependencies
 */
import { createNewPost, visitAdminPage, activatePlugin, deactivatePlugin } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { cleanUpSettings } from '../../utils/onboarding-wizard-utils';

const postPreviewBtnSelector = '.components-button.editor-post-preview';
const ampPreviewBtnSelector = `${ postPreviewBtnSelector } + .amp-wrapper-post-preview > .amp-editor-post-preview`;

describe( 'AMP Preview button', () => {
	it( 'is rendered on a new post', async () => {
		await createNewPost();
		await page.waitForSelector( postPreviewBtnSelector );

		await expect( page ).toMatchElement( ampPreviewBtnSelector );
	} );

	it( 'is rendered when Gutenberg is disabled', async () => {
		await deactivatePlugin( 'gutenberg' );

		await createNewPost();
		await page.waitForSelector( postPreviewBtnSelector );

		await expect( page ).toMatchElement( ampPreviewBtnSelector );

		await activatePlugin( 'gutenberg' );
	} );

	it( 'is rendered when a post has content', async () => {
		await createNewPost( {
			title: 'The Ballad of the Lost Preview Button',
			content: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consectetur fugiat, impedit.',
		} );
		await page.waitForSelector( postPreviewBtnSelector );

		await expect( page ).toMatchElement( ampPreviewBtnSelector );
	} );

	it( 'does not render the button when in Standard mode', async () => {
		// Set theme support to Standard mode.
		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await page.waitForSelector( '.settings-footer' );
		await page.evaluate( async () => {
			await wp.apiFetch( { path: '/amp/v1/options', method: 'POST', data: { theme_support: 'standard' } } );
		} );

		await createNewPost();
		await page.waitForSelector( postPreviewBtnSelector );

		await expect( page ).not.toMatchElement( ampPreviewBtnSelector );

		await cleanUpSettings();
	} );
} );
