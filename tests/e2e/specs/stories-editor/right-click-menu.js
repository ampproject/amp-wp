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

	beforeEach( async() => {
		await createNewPost( { postType: 'amp_story' } );
	} );

	it( 'opens the right click menu with the block actions when clicking on a block', async () => {
	} );

	it( 'does not open the menu by clicking on a page', async () => {
	} );

	it( 'should open right click menu for pasting on a page if a block has been copied previously', async () => {
	} );

	it( 'should allow copying and pasting a block', async () => {
	} );

	it( 'should allow cutting and pasting a block', async () => {
	} );

	it( 'should allow duplicating a block', async () => {
	} );

	it( 'should allow removing a block', async () => {
	} );

	it( 'should close the menu when clicking anywhere outside of the menu', async () => {
	} );
} );
