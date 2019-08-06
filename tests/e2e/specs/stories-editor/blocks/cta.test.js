/**
 * WordPress dependencies
 */
import { visitAdminPage, createNewPost } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { insertBlock, activateExperience, deactivateExperience } from '../../../utils';

describe( 'Stories Editor Screen', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	it( 'should not display CTA icon when only one Page is present', async () => {
		await visitAdminPage( 'post-new.php', 'post_type=amp_story' );

		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes ).toHaveLength( 0 );
	} );

	it( 'should display CTA icon when second Page is added', async () => {
		await createNewPost( { postType: 'amp_story' } );

		await insertBlock( 'Page' );
		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes ).toHaveLength( 1 );
	} );
} );
