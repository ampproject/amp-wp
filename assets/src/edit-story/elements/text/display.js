/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef, useEffect, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import getCaretCharacterOffsetWithin from '../../utils/getCaretCharacterOffsetWithin';
import { useStory } from '../../app';
import { useCanvas } from '../../components/canvas';
import {
	ElementWithPosition,
	ElementWithSize,
	ElementWithRotation,
	ElementWithFont,
	ElementWithBackgroundColor,
	ElementWithFontColor,
} from '../shared';

const Element = styled.p`
	margin: 0;
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	${ ElementWithFont }
	${ ElementWithBackgroundColor }
	${ ElementWithFontColor }

	user-select: ${ ( { canSelect } ) => canSelect ? 'initial' : 'none' };

	&:focus {
		outline: none;
	}
`;

function TextDisplay( { id, content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle, rotationAngle, setClickHandler, forwardedRef, onPointerDown } ) {
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
		ref: forwardedRef,
		onPointerDown,
	};
	const {
		state: { selectedElementIds },
	} = useStory();
	const {
		actions: { setEditingElement, setEditingElementWithState },
	} = useCanvas();
	const isElementSelected = selectedElementIds.includes( id );
	const isElementOnlySelection = isElementSelected && selectedElementIds.length === 1;
	const handleClick = useCallback( ( evt ) => {
		evt.persist();
		if ( evt.shiftKey || evt.metaKey || evt.altKey || evt.ctrlKey ) {
			// Some modifier was pressed. Ignore and bubble
			return;
		}
		// Enter editing without and place cursor at current selection offset
		setEditingElementWithState( id, { offset: getCaretCharacterOffsetWithin( element.current, evt.clientX, evt.clientY ) } );
		evt.stopPropagation();
	}, [ id, setEditingElementWithState ] );
	useEffect( () => {
		if ( setClickHandler ) {
			setClickHandler( id, handleClick );
		}
	}, [ id, setClickHandler, handleClick ] );

	const handleKeyDown = ( evt ) => {
		if ( evt.metaKey || evt.altKey || evt.ctrlKey ) {
			// Some modifier (except shift) was pressed. Ignore and bubble
			return;
		}

		if ( evt.key === 'Enter' ) {
			// Enter editing without writing or selecting anything
			setEditingElement( id );
			evt.stopPropagation();
			// Make sure no actual Enter is pressed
			evt.preventDefault();
		} else if ( /^\w$/.test( evt.key ) ) {
			// TODO: in above check all printable characters across alphabets, no just a-z0-9 as \w is
			// Enter editing and clear content (first letter will be correctly inserted from keyup)
			setEditingElementWithState( id, { clearContent: true } );
			evt.stopPropagation();
		}

		// ignore everything else and bubble.
	};
	const onKeyDown = isElementOnlySelection ? handleKeyDown : null;
	const tabIndex = isElementOnlySelection ? 0 : null;
	const element = useRef();
	useEffect( () => {
		if ( isElementOnlySelection && element.current ) {
			element.current.focus();
		}
	}, [ isElementOnlySelection ] );
	return (
		<Element
			canSelect={ isElementOnlySelection }
			onKeyDown={ onKeyDown }
			tabIndex={ tabIndex }
			ref={ element }
			dangerouslySetInnerHTML={ { __html: content } }
			{ ...props }
		/>
	);
}

TextDisplay.propTypes = {
	id: PropTypes.string.isRequired,
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
	setClickHandler: PropTypes.func,
	forwardedRef: PropTypes.func,
	onPointerDown: PropTypes.func,
};

export default TextDisplay;
