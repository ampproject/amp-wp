/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';
import { Editor, EditorState, SelectionState } from 'draft-js';
import { stateFromHTML } from 'draft-js-import-html';
import { stateToHTML } from 'draft-js-export-html';

/**
 * WordPress dependencies
 */
import { useState, useEffect, useLayoutEffect, useRef, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory, useFont } from '../../app';
import { useCanvas } from '../../components/canvas';
import {
	ElementWithPosition,
	ElementWithSize,
	ElementWithRotation,
	ElementWithFont,
	ElementWithBackgroundColor,
	ElementWithFontColor,
} from '../shared';
import { getFilteredState, getHandleKeyCommand, maybeEnqueueFontStyle } from './util';

const Element = styled.div`
	margin: 0;
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	${ ElementWithFont }
	${ ElementWithBackgroundColor }
	${ ElementWithFontColor }

	&::after {
		content: '';
		display: block;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		border: 1px solid ${ ( { theme } ) => theme.colors.mg.v1 }70;
		pointer-events: none;
	}
`;

function TextEdit( { content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle, rotationAngle } ) {
	const props = {
		color,
		backgroundColor,
		fontFamily,
		fontStyle,
		fontSize,
		fontWeight,
		width,
		height,
		x,
		y,
		rotationAngle,
	};
	const { actions: { setPropertiesOnSelectedElements } } = useStory();
	const { actions: { getFont } } = useFont();
	const { state: { editingElementState } } = useCanvas();
	const { offset, clearContent } = editingElementState || {};
	// To clear content, we can't just use createEmpty() or even pure white-space.
	// The editor needs some content to insert the first character in,
	// so we use a non-breaking space instead and trim it on save if still present.
	const EMPTY_VALUE = '\u00A0';
	const initialState = (
		clearContent ?
			EditorState.createWithContent( stateFromHTML( EMPTY_VALUE ) ) :
			EditorState.createWithContent( stateFromHTML( content ) )
	);
	const [ editorState, setEditorState ] = useState( initialState );
	const mustAddOffset = useRef( offset ? 2 : 0 );

	// This is to allow the finalizing useEffect to *not* depend on editorState,
	// as would otherwise be a lint error.
	const lastKnownState = useRef( null );

	// This filters out illegal content (see `getFilteredState`)
	// on paste and updates state accordingly.
	// Furthermore it also sets initial selection if relevant.
	const updateEditorState = useCallback( ( newEditorState ) => {
		let filteredState = getFilteredState( newEditorState, editorState );
		if ( mustAddOffset.current ) {
			// For some reason forced selection only sticks the second time around?
			// Several other checks have been attempted here without success.
			// Optimize at your own perril!
			mustAddOffset.current--;
			const key = filteredState.getCurrentContent().getFirstBlock().getKey();
			const selectionState = new SelectionState( { anchorKey: key, anchorOffset: offset } );
			filteredState = EditorState.forceSelection( filteredState, selectionState );
		}
		lastKnownState.current = filteredState.getCurrentContent();
		setEditorState( filteredState );
	}, [ editorState, offset ] );

	// Finally update content for element on unmount.
	useEffect( () => () => {
		if ( setPropertiesOnSelectedElements && lastKnownState.current ) {
			// Remember to trim any trailing non-breaking space.
			setPropertiesOnSelectedElements( {
				content: stateToHTML( lastKnownState.current, { defaultBlockTag: null } )
					.replace( /&nbsp;$/, '' ),
			} );
		}
	}, [ setPropertiesOnSelectedElements ] );

	// Make sure to allow the user to click in the text box while working on the text.
	const onClick = ( evt ) => evt.stopPropagation();

	// Handle basic key commands such as bold, italic and underscore.
	const handleKeyCommand = getHandleKeyCommand( setEditorState );

	// Set focus when initially rendered
	const editor = useRef( null );
	useLayoutEffect( () => {
		editor.current.focus();
	}, [] );

	useEffect( () => {
		maybeEnqueueFontStyle( fontFamily, getFont );
	}, [ fontFamily, getFont ] );

	return (
		<Element { ...props } onClick={ onClick }>
			<Editor
				ref={ editor }
				onChange={ updateEditorState }
				editorState={ editorState }
				handleKeyCommand={ handleKeyCommand }
			/>
		</Element>
	);
}

TextEdit.propTypes = {
	content: PropTypes.string,
	color: PropTypes.string,
	backgroundColor: PropTypes.string,
	fontFamily: PropTypes.string,
	fontSize: PropTypes.string,
	fontWeight: PropTypes.string,
	fontStyle: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default TextEdit;
