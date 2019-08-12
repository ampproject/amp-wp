/**
 * WordPress dependencies
 */
import { createNewPost, switchEditorModeTo } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience } from '../../utils';

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

		await switchEditorModeTo( 'Code' );
		await page.click( '.edit-post-more-menu [aria-label="More tools & options"]' );

		await expect( page ).not.toMatchElement( '#amp-story-block-navigation' );

		await switchEditorModeTo( 'Visual' );
		await page.click( '.edit-post-more-menu [aria-label="More tools & options"]' );

		await page.waitForSelector( '#amp-story-block-navigation' );

		// Make sure that the block navigation is only rendered once.
		const blockNavigationElementCount = await page.$$eval( '#amp-story-block-navigation', ( templates ) => templates.length );
		expect( blockNavigationElementCount ).toStrictEqual( 1 );

		await expect( page ).not.toMatchElement( '.block-editor-writing-flow__click-redirect' );
	} );
} );
