/**
 * WordPress dependencies
 */
import { createNewPost } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, switchEditorModeTo } from '../../utils';

describe( 'Code Editor', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	it( 'allows switching to code editor and back', async () => {
		await createNewPost( { postType: 'amp_story' } );

		await page.waitForSelector( '#amp-story-block-navigation' );

		await expect( page ).not.toMatchElement( '.block-editor-writing-flow__click-redirect' );

		// Ensure the menu is available before switching mode.
		await page.waitForSelector( '.edit-post-more-menu [aria-label="More tools & options"]' );
		await switchEditorModeTo( 'Code' );
		await page.click( '.edit-post-more-menu [aria-label="More tools & options"]' );

		await expect( page ).not.toMatchElement( '#amp-story-block-navigation' );

		await expect( page ).toMatchElement( '.block-editor-inserter .block-editor-inserter__toggle[disabled]' );
		await expect( page ).toMatchElement( '#amp-story-shortcuts .components-icon-button[disabled]' );

		await switchEditorModeTo( 'Visual' );
		await page.click( '.edit-post-more-menu [aria-label="More tools & options"]' );

		await page.waitForSelector( '#amp-story-block-navigation' );

		// Make sure that the block navigation is only rendered once.
		const blockNavigationElementCount = await page.$$eval( '#amp-story-block-navigation', ( templates ) => templates.length );
		expect( blockNavigationElementCount ).toStrictEqual( 1 );

		await expect( page ).not.toMatchElement( '.block-editor-writing-flow__click-redirect' );
	} );
} );
