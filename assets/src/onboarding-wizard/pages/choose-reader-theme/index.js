/**
 * WordPress dependencies
 */
import { useEffect, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';
import { ReaderThemes } from '../../../components/reader-themes-context-provider';
import { Options } from '../../../components/options-context-provider';
import { Loading } from '../../../components/loading';
import { ReaderThemeSelection } from '../../../components/reader-theme-selection';

/**
 * Screen for choosing the Reader theme.
 */
export function ChooseReaderTheme() {
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { editedOptions } = useContext( Options );
	const { fetchingThemes, themes } = useContext( ReaderThemes );

	const { reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if (
			themes &&
			readerTheme &&
			canGoForward === false &&
			themes.map( ( { slug } ) => slug ).includes( readerTheme )
		) {
			setCanGoForward( true );
		}
	}, [ canGoForward, setCanGoForward, readerTheme, themes, themeSupport ] );

	if ( fetchingThemes ) {
		return (
			<Loading />
		);
	}

	return (
		<div className="choose-reader-theme">
			<ReaderThemeSelection />
		</div>
	);
}
