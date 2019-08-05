/**
 * WordPress dependencies
 */
import { createNewPost, getAllBlocks, selectBlockByClientId } from '@wordpress/e2e-test-utils';

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

		// Select the page block.
		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);

		// Click the 'More tools & options' button to expand the popover.
		await page.click( '.components-button.components-icon-button.components-dropdown-menu__toggle' );

		await expect( page ).not.toMatch( 'Block Manager' );
	} );
} );
