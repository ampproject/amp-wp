/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { insertStoryBlockBySearch, toggleStories } from '../utils';

describe( 'Stories Editor Screen', () => {
	beforeAll( async () => {
		await toggleStories( true );
	} );

	afterAll( async () => {
		await toggleStories( false );
	} );

	it( 'Should not display CTA icon when only one Page is present', async () => {
		await visitAdminPage( 'post-new.php', 'post_type=amp_story' );

		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes.length ).toEqual( 0 );
	} );

	it( 'Should display CTA icon when second Page is added', async () => {
		await visitAdminPage( 'post-new.php', 'post_type=amp_story' );

		await insertStoryBlockBySearch( 'Page' );

		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes.length ).toEqual( 1 );
	} );
} );
