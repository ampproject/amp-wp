/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useContext } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';

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
	const instanceId = useInstanceId( ChooseReaderTheme );
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { options } = useContext( Options );
	const { fetchingThemes, setShouldFetchThemes, shouldFetchThemes, themes, themeFetchError } = useContext( ReaderThemes );

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

	/**
	 * Triggers fetching themes if they have not yet been fetched.
	 */
	useEffect( () => {
		if ( ! shouldFetchThemes ) {
			setShouldFetchThemes( true );
		}
	}, [ setShouldFetchThemes, shouldFetchThemes ] );

	if ( themeFetchError ) {
		return (
			<p>
				{ __( 'There was an error fetching theme data.', 'amp' ) }
			</p>
		);
	}

	if ( fetchingThemes ) {
		return (
			<Loading />
		);
	}

	console.log( themes );
	return (
		<div className="amp-wp-choose-reader-theme">
			<p>
				{
					// @todo Probably improve this text.
					__( 'Select a theme to use on AMP-compatible pages on mobile devices', 'amp' )
				}
			</p>
			<form>
				<ul className="amp-wp-choose-reader-theme__grid">
					{
						themes && themes.map( ( theme ) => (
							<ThemeCard
								key={ `${ instanceId }-${ theme.slug }` }
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
