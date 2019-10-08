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
 * @param {Element}  textElement         Containing text element.
 * @param {Element}  ampFitTextElement   Element that contains the text.
 * @param {number}   appliedWidth        Width from resizing.
 * @param {number}   appliedHeight       Height from resizing.
 * @param {boolean}  isText              If the block is a Text block.
 * @param {boolean}  blockLimitsReached  If the block's default limits have been reached.
 *
 */
const adjustFontSizeWhileResizing = ( textElement, ampFitTextElement, appliedWidth, appliedHeight, isText, blockLimitsReached ) => {
	let scrollWidth = textElement.scrollWidth;
	let scrollHeight = textElement.scrollHeight;

	// For other than text blocks, let's set the height for being able to measure correctly.
	if ( ! isText ) {
		ampFitTextElement.style.height = 'initial';
	} else {
		textElement.style.height = 'auto';
	}

	let fontSize = parseInt( ampFitTextElement.style.fontSize );

	const contentExceedsLimits = ( contentWidth, contentHeight ) => {
		const buffer = 3;
		return Math.round( appliedWidth ) < contentWidth || Math.round( appliedHeight ) < contentHeight - buffer;
	};

	const contentLimitReached = ( contentWidth, contentHeight ) => {
		const buffer = 5;
		return Math.round( appliedWidth ) <= contentWidth || Math.round( appliedHeight ) <= ( contentHeight - buffer );
	};

	if ( contentExceedsLimits( scrollWidth, scrollHeight ) ) {
		while ( contentExceedsLimits( scrollWidth, scrollHeight ) && fontSize > MIN_FONT_SIZE ) {
			fontSize--;
			ampFitTextElement.style.fontSize = fontSize + 'px';
			scrollWidth = ampFitTextElement.scrollWidth;
			scrollHeight = ampFitTextElement.scrollHeight;
		}
	} else if ( ! blockLimitsReached ) {
		while ( ! contentLimitReached( scrollWidth, scrollHeight ) && fontSize < MAX_FONT_SIZE ) {
			fontSize++;
			ampFitTextElement.style.fontSize = fontSize + 'px';
			scrollWidth = ampFitTextElement.scrollWidth;
			scrollHeight = ampFitTextElement.scrollHeight;
		}
	}

	// Reset the height.
	if ( ! isText ) {
		ampFitTextElement.style.height = '';
	} else {
		textElement.style.height = '';
	}
};

export default adjustFontSizeWhileResizing;
