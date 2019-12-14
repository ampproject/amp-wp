/**
 * WordPress dependencies
 */
import { createNewPost } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience } from '../../utils';

describe( 'Block Manager', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	it( 'the button should not appear, due to styling of display: none', async () => {
		await createNewPost( { postType: 'amp_story' } );

		// Click the 'More tools & options' button to expand the popover.
		await page.click( '.edit-post-more-menu .components-dropdown-menu__toggle' );

		// The text 'Block Manager' should not be present.
		const blockManagerButton = await page.waitForXPath( '//button[contains(text(), "Block Manager")]' );
		expect( await blockManagerButton.isIntersectingViewport() ).toStrictEqual( false );
	} );
} );
