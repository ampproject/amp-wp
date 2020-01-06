/**
 * External dependencies
 */
import { RichUtils } from 'draft-js';
import { filterEditorState } from 'draftjs-filters';

export function getFilteredState( editorState, oldEditorState ) {
	const shouldFilterPaste =
		oldEditorState.getCurrentContent() !== editorState.getCurrentContent() &&
		editorState.getLastChangeType() === 'insert-fragment';

	if ( ! shouldFilterPaste ) {
		return editorState;
	}

	return filterEditorState(
		{
			blocks: [],
			styles: [ 'BOLD', 'ITALIC', 'UNDERLINE' ],
			entities: [],
			maxNesting: 1,
			whitespacedCharacters: [],
		},
		editorState,
	);
}

const ALLOWED_KEY_COMMANDS = [
	'bold',
	'italic',
	'underline',
];
export const getHandleKeyCommand = ( setEditorState ) => ( command, currentEditorState ) => {
	if ( ! ALLOWED_KEY_COMMANDS.includes( command ) ) {
		return 'not-handled';
	}
	const newEditorState = RichUtils.handleKeyCommand(
		currentEditorState,
		command,
	);
	if ( newEditorState ) {
		setEditorState( newEditorState );
		return 'handled';
	}
	return 'not-handled';
};

/**
 * Adds a <link> element to the <head> for a given font in case there is none yet.
 *
 * Allows dynamically enqueuing font styles when needed.
 *
 * @param {string} name Font name.
 * @param {Function} getFont Function to get current font.
 */
export const maybeEnqueueFontStyle = ( name, getFont ) => {
	if ( ! name ) {
		return;
	}

	const font = getFont( name );
	if ( ! font ) {
		return;
	}

	const { handle, src } = font;
	if ( ! handle || ! src ) {
		return;
	}

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
	fontStylesheet.crossOrigin = 'anonymous';

	document.head.appendChild( fontStylesheet );
};
