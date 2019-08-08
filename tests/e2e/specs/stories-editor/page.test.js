/**
 * External dependencies
 */
import { first } from 'lodash';

/**
 * WordPress dependencies
 */
import { createNewPost, saveDraft, findSidebarPanelToggleButtonWithTitle } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	deactivateExperience,
	clickButtonByLabel,
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
	} );

	it( 'should be possible to add background color to Page', async () => {
		const panelTitle = 'Background Color';
		await page.waitForSelector( `//div[contains(@class,"edit-post-sidebar")]//button[@class="components-button components-panel__body-toggle"][contains(text(),"${ panelTitle }")]` );
		const backgroundColorPanel = first( await page.$x( `//div[contains(@class,"edit-post-sidebar")]//button[@class="components-button components-panel__body-toggle"][contains(text(),"${ panelTitle }")]` ) );
		expect( backgroundColorPanel ).toBeDefined();

		await backgroundColorPanel.click( 'button' );
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
	} );

	it( 'should allow adding opacity', async () => {
	} );

	it( 'should be possible to add Background Image', async () => {
	} );

	it( 'should be possible to add Background Video', async () => {
	} );

	it( 'should save tha page advancement setting correctly', async () => {

	} );

	it( 'should consider animations time when setting the page advancement', async () => {

	} );
} );
