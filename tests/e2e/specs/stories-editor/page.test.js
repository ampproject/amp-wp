/**
 * WordPress dependencies
 */
import { createNewPost, saveDraft, clickButton } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	deactivateExperience,
	clickButtonByLabel,
	selectBlockByClassName,
	getSidebarPanelToggleByTitle,
} from '../../utils';

describe( 'Story Page', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
		await selectBlockByClassName( 'amp-page-active' );
	} );

	it( 'should be possible to add background color to Page', async () => {
		const panelTitle = 'Background Color';
		const colorToggle = await getSidebarPanelToggleByTitle( panelTitle );

		await colorToggle.click();

		await clickButtonByLabel( 'Color: Vivid red' );

		await saveDraft();
		await page.reload();

		const style = 'background-color: rgb(207, 46, 46); opacity: 1;';

		const nodes = await page.$x(
			`//div[contains(@style,"${ style }")]`
		);
		expect( nodes.length ).not.toEqual( 0 );
	} );

	it( 'should allow adding gradient', async () => {
		const panelTitle = 'Background Color';
		const colorToggle = await getSidebarPanelToggleByTitle( panelTitle );

		await colorToggle.click();
		await clickButtonByLabel( 'Color: Vivid red' );
		await clickButton( 'Add Gradient' );

		await saveDraft();
		await page.reload();

		const style = 'background-image: linear-gradient(rgb(207, 46, 46), transparent)';

		const nodes = await page.$x(
			`//div[contains(@style,"${ style }")]`
		);
		expect( nodes.length ).not.toEqual( 0 );
	} );
	/*
		it( 'should allow adding opacity', async () => {
		} );

		it( 'should be possible to add Background Image', async () => {
		} );

		it( 'should be possible to add Background Video', async () => {
		} );

		it( 'should save tha page advancement setting correctly', async () => {

		} );

		it( 'should consider animations time when setting the page advancement', async () => {

		} );*/
} );
