/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useFont } from '../../app';
import {
	ElementFillContent,
	ElementWithFont,
	ElementWithBackgroundColor,
	ElementWithFontColor,
	ElementWithStyle,
} from '../shared';
import { generateFontFamily } from './util';

const Element = styled.p`
	margin: 0;
	${ ElementFillContent }
	${ ElementWithFont }
	${ ElementWithBackgroundColor }
	${ ElementWithFontColor }
	${ ElementWithStyle }
`;

function TextDisplay( {
	content,
	color,
	backgroundColor,
	fontFamily,
	fontFallback,
	fontSize,
	fontWeight,
	fontStyle,
	letterSpacing,
	lineHeight,
	padding,
	textAlign,
} ) {
	const props = {
		color,
		backgroundColor,
		fontFamily: generateFontFamily( fontFamily, fontFallback ),
		fontFallback,
		fontStyle,
		fontSize,
		fontWeight,
		letterSpacing,
		lineHeight,
		padding,
		textAlign,
	};
	const {
		actions: { maybeEnqueueFontStyle },
	} = useFont();

	useEffect( () => {
		maybeEnqueueFontStyle( fontFamily );
	}, [ fontFamily, maybeEnqueueFontStyle ] );

	return (
		<Element
			dangerouslySetInnerHTML={ { __html: content } }
			{ ...props }
		/>
	);
}

TextDisplay.propTypes = {
	content: PropTypes.string,
	color: PropTypes.string,
	backgroundColor: PropTypes.string,
	fontFamily: PropTypes.string,
	fontFallback: PropTypes.array,
	fontSize: PropTypes.number,
	fontWeight: PropTypes.number,
	fontStyle: PropTypes.string,
	letterSpacing: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
	lineHeight: PropTypes.number,
	padding: PropTypes.number,
	textAlign: PropTypes.string,
};

export default TextDisplay;
