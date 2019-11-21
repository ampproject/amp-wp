/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';
import { Editor, EditorState } from 'draft-js';
import { stateFromHTML } from 'draft-js-import-html';
import { stateToHTML } from 'draft-js-export-html';

/**
 * WordPress dependencies
 */
import { useState, useEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import {
	ElementWithPosition,
	ElementWithSize,
	ElementWithFont,
	ElementWithBackgroundColor,
	ElementWithFontColor,
} from '../shared';
import { getFilteredState, getHandleKeyCommand } from './util';

const Element = styled.div`
	margin: 0;
	${ ElementWithPosition }
	${ ElementWithSize }
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

function TextEdit( { content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle } ) {
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
	};
	const initialState = EditorState.createWithContent( stateFromHTML( content ) );
	const [ editorState, setEditorState ] = useState( initialState );
	const { actions: { setPropertiesOnSelectedElements } } = useStory();

	// This is to allow the useEffect to *not* depend on editorState,
	// as would otherwise be a lint error.
	const lastKnownState = useRef( null );

	// This filters out illegal content on paste and updates state accordingly.
	const updateEditorState = ( newEditorState ) => {
		const filteredState = getFilteredState( newEditorState, editorState );
		lastKnownState.current = filteredState.getCurrentContent();
		setEditorState( filteredState );
	};

	// Finally update content for element on unmount.
	useEffect( () => () => {
		if ( setPropertiesOnSelectedElements && lastKnownState.current ) {
			setPropertiesOnSelectedElements( {
				content: stateToHTML( lastKnownState.current, { defaultBlockTag: null } ),
			} );
		}
	}, [ setPropertiesOnSelectedElements ] );

	// Make sure to allow the user to click in the text box while working on the text.
	const onClick = ( evt ) => evt.stopPropagation();

	// Handle basic key commands such as bold, italic and underscore.
	const handleKeyCommand = getHandleKeyCommand( setEditorState );

	return (
		<Element { ...props } onClick={ onClick }>
			<Editor
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
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default TextEdit;
