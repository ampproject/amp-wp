/**
 * WordPress dependencies
 */
import { createNewPost, activatePlugin, deactivatePlugin } from '@wordpress/e2e-test-utils';

describe( 'Enable AMP Toggle', () => {
	it( 'should display even when Gutenberg is not active', async () => {
		await deactivatePlugin( 'gutenberg' );
		await createNewPost();

		// Open the AMP panel if collapsed.
		const [ collapsedPanel ] = await page.$x( '//button[ contains( @class, "components-panel__body-toggle" ) and @aria-expanded="false" and contains( text(), "AMP" ) ]' );
		if ( collapsedPanel ) {
			await collapsedPanel.click();
		}

		await expect( page ).toMatchElement( 'label[for^="amp-toggle-"]', { text: 'Enable AMP' } );

		await activatePlugin( 'gutenberg' );
	} );
} );
