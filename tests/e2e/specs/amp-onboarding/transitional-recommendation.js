/**
 * Internal dependencies
 */
import { moveToReaderThemesScreen, moveToDoneScreen } from '../../utils/onboarding-wizard-utils';

/**
 * When selecting Reader mode, the list of themes should no longer omit the active theme from the list.
 * Instead, if the user selects the active theme to be the Reader theme, then the template mode should be automatically
 * switched from reader to transitional, and a notice can appear on the summary screen to make them aware of this.
 *
 * The active theme in test environment is twentytwenty.
 *
 * @see https://github.com/ampproject/amp-wp/issues/4975
 */
describe( 'Current active theme is reader theme and user is nontechnical', () => {
	it( 'includes active theme in reader theme list', async () => {
		await moveToReaderThemesScreen( { technical: false } );

		await expect( page ).toMatchElement( '[for="theme-card__twentytwenty"]' );
	} );

	it( 'switches to transitional mode and shows a notice if the user chooses the active theme', async () => {
		await moveToDoneScreen( { technical: false, readerTheme: 'twentytwenty', mode: 'reader' } );

		const stepperItemCount = await page.$$eval( '.amp-stepper__item', ( els ) => els.length );
		expect( stepperItemCount ).toBe( 5 );

		await expect( page ).toMatchElement( 'p', { text: /transitional mode/i } );
		await expect( page ).toMatchElement( '.amp-notice--info', { text: /switched to Transitional/i } );
	} );
} );

