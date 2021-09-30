/**
 * Internal dependencies
 */
import { moveToReaderThemesScreen, moveToTemplateModeScreen, moveToDoneScreen } from '../../utils/onboarding-wizard-utils';

/**
 * When a site has a Reader theme already set as the active theme (e.g. Twenty Twenty), when the user expresses they
 * are non-technical then both the Reader mode and the Transitional mode should show as ✅ recommended options.
 *
 * Additionally, when selecting Reader mode, the list of themes should no longer omit the active theme from the list.
 * Instead, if the user selects the active theme to be the Reader theme, then the template mode should be automatically
 * switched from reader to transitional, and a notice can appear on the summary screen to make them aware of this.
 *
 * The active theme in test environment is twentytwenty.
 *
 * @see https://github.com/ampproject/amp-wp/issues/4975
 */
describe( 'Current active theme is reader theme and user is nontechnical', () => {
	it( 'correctly recommends transitional when the user is nontechnical and the active theme is a reader theme', async () => {
		await moveToTemplateModeScreen( { technical: false } );

		await expect( '.amp-notice--info' ).countToBe( 1 ); // Standard.
		await expect( '.amp-notice--success' ).countToBe( 2 ); // Reader and transitional.
	} );

	it( 'includes active theme in reader theme list', async () => {
		await moveToReaderThemesScreen( { technical: false } );

		await expect( page ).toMatchElement( '[for="theme-card__twentytwenty"]' );
	} );

	it( 'switches to transitional mode and shows a notice if the user chooses the active theme', async () => {
		await moveToDoneScreen( { technical: false, readerTheme: 'twentytwenty', mode: 'reader' } );

		const stepperItemCount = await page.$$eval( '.amp-stepper__item', ( els ) => els.length );
		expect( stepperItemCount ).toBe( 4 );

		// Wait for the settings to get saved.
		await page.waitForTimeout( 1000 );

		await expect( page ).toMatchElement( 'p', { text: /transitional mode/i } );
		await expect( page ).toMatchElement( '.amp-notice--info', { text: /switched to Transitional/i } );
	} );
} );

