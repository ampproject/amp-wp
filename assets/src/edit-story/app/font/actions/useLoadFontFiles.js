/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

function useLoadFontFiles( { getFontByName } ) {
	/**
	 * Adds a <link> element to the <head> for a given font in case there is none yet.
	 *
	 * Allows dynamically enqueuing font styles when needed.
	 *
	 * @param {string} name Font name.
	 */
	const maybeEnqueueFontStyle = useCallback( ( name ) => {
		if ( ! name ) {
			return;
		}

		const font = getFontByName( name );
		if ( ! font ) {
			return;
		}

		const { handle, src } = font;
		if ( ! handle || ! src ) {
			return;
		}
		const id = `${ handle }-css`;
		const element = document.getElementById( id );

		if ( element ) {
			return;
		}

		const fontStylesheet = document.createElement( 'link' );
		fontStylesheet.id = id;
		fontStylesheet.href = src;
		fontStylesheet.rel = 'stylesheet';
		fontStylesheet.type = 'text/css';
		fontStylesheet.media = 'all';
		fontStylesheet.crossOrigin = 'anonymous';

		document.head.appendChild( fontStylesheet );
	}, [ getFontByName ] );

	return maybeEnqueueFontStyle;
}

export default useLoadFontFiles;
