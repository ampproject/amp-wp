/**
 * Internal dependencies
 */
import { ReaderThemeCarousel } from '../components/reader-theme-carousel';

/**
 * The reader themes section of the settings page.
 */
export function ReaderThemes() {
	return <ReaderThemeCarousel hideCurrentlyActiveTheme={ true } />;
}
