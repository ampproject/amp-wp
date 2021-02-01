/**
 * External dependencies
 */
import { LEGACY_THEME_SLUG, AMP_QUERY_VAR_CUSTOMIZED_LATE } from 'amp-settings'; // From WP inline script.

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
			! AMP_QUERY_VAR_CUSTOMIZED_LATE
				? themes.map( ( { slug } ) => slug ).includes( readerTheme )
				: readerTheme === LEGACY_THEME_SLUG
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
