export const maybeEnqueueFontStyle = ( slug ) => {
	if ( ! slug || ! window.ampStoriesGoogleFonts[ slug ] ) {
		return;
	}

	const { handle, src } = window.ampStoriesGoogleFonts[ slug ];

	const element = document.getElementById( handle );

	if ( element ) {
		return;
	}

	const fontStylesheet = document.createElement( 'link' );
	fontStylesheet.id = handle;
	fontStylesheet.href = src;
	fontStylesheet.rel = 'stylesheet';
	fontStylesheet.type = 'text/css';
	fontStylesheet.media = 'all';

	document.head.appendChild( fontStylesheet );
};
