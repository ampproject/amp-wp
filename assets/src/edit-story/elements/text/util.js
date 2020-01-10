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

export const generateFontFamily = ( fontFamily, fontFallback ) => {
	let fontFamilyDisplay = fontFamily ? `${ fontFamily }` : null;
	if ( fontFallback && fontFallback.length ) {
		fontFamilyDisplay += fontFamily ? `,` : ``;
		fontFamilyDisplay += `${ fontFallback.join( `,` ) }`;
	}
	return fontFamilyDisplay;
};
