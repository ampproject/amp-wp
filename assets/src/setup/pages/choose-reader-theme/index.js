/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Loading } from '../../components/loading';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../components/options-context-provider';
import { ReaderThemes } from '../../components/reader-themes-context-provider';
import { ThemeCard } from './theme-card';

/**
 * Screen for choosing the Reader theme.
 */
export function ChooseReaderTheme() {
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { options } = useContext( Options );
	const { fetchingThemes, themes } = useContext( ReaderThemes );

	const { reader_theme: readerTheme } = options || {};

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( themes && readerTheme && canGoForward === false ) {
			if ( themes.map( ( { slug } ) => slug ).includes( readerTheme ) ) {
				setCanGoForward( true );
			}
		}
	}, [ canGoForward, setCanGoForward, readerTheme, themes ] );

	if ( fetchingThemes ) {
		return (
			<Loading />
		);
	}

	return (
		<div className="choose-reader-theme">
			<p>
				{
					// @todo Probably improve this text.
					__( 'Select the theme template for mobile visitors', 'amp' )
				}
			</p>
			<form>
				<ul className="choose-reader-theme__grid">
					{
						themes && themes.map( ( theme ) => (
							<ThemeCard
								key={ `theme-card-${ theme.slug }` }
								screenshotUrl={ theme.screenshot_url }
								{ ...theme }
							/> ),
						)
					}
				</ul>
			</form>
		</div>
	);
}
