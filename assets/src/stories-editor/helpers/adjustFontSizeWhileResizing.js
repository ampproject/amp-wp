/**
 * Internal dependencies
 */
import { MAX_FONT_SIZE, MIN_FONT_SIZE } from '../../common/constants';

/**
 * Helper to adjust the font size while resizing for elements that support amp-fit-text.
 *
 * Reduces the font size when reducing the size and increases when increasing.
 * This is not intended to assign the ideal match as amp-fit-text does
 * after resizing stops, however, should come quite close.
 *
 * @param {Element}  textElement       Containing text element.
 * @param {Element}  ampFitTextElement Element that contains the text.
 * @param {number}   appliedWidth      Width from resizing.
 * @param {number}   appliedHeight     Height from resizing.
 *
 */
const adjustFontSizeWhileResizing = ( textElement, ampFitTextElement, appliedWidth, appliedHeight ) => {
	let scrollWidth = textElement.scrollWidth;
	let scrollHeight = textElement.scrollHeight;

	let fontSize = parseInt( ampFitTextElement.style.fontSize );
	if ( appliedWidth < scrollWidth || appliedHeight < scrollHeight ) {
		while ( ( appliedWidth < scrollWidth || appliedHeight < scrollHeight ) && fontSize > MIN_FONT_SIZE ) {
			fontSize--;
			ampFitTextElement.style.fontSize = fontSize + 'px';
			scrollWidth = ampFitTextElement.scrollWidth;
			scrollHeight = ampFitTextElement.scrollHeight;
		}
	} else {
		let limitReached = false;
		while ( ! limitReached && fontSize < MAX_FONT_SIZE ) {
			ampFitTextElement.style.fontSize = ( fontSize + 1 ) + 'px';
			scrollWidth = ampFitTextElement.scrollWidth;
			scrollHeight = ampFitTextElement.scrollHeight;
			if ( appliedWidth < scrollWidth || appliedHeight < scrollHeight ) {
				ampFitTextElement.style.fontSize = fontSize + 'px';
				limitReached = true;
			} else {
				fontSize++;
			}
		}
	}
};

export default adjustFontSizeWhileResizing;
