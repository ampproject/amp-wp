/**
 * WordPress dependencies
 */
import { count } from '@wordpress/wordcount';
import { _x } from '@wordpress/i18n';

// Todo: Make these customizable?
const H1_FONT_SIZE = 40;
const H2_FONT_SIZE = 24;
const H1_TEXT_LENGTH = 4;
const H2_TEXT_LENGTH = 10;

/*
 * translators: If your word count is based on single characters (e.g. East Asian characters),
 * enter 'characters_excluding_spaces' or 'characters_including_spaces'. Otherwise, enter 'words'.
 * Do not translate into your own language.
 */
const wordCountType = _x( 'words', 'Word count type. Do not translate!', 'amp' );

/**
 * Determines the HTML tag name that should be used given on the block's attributes.
 *
 * Font size takes precedence over text length as it's a stronger signal for semantic meaning.
 *
 * @param {Object} attributes Block attributes.
 * @return {string} HTML tag name. Either p, h1, or h2.
 */
export default function( attributes ) {
	const { type, fontSize, customFontSize } = attributes;

	if ( -1 !== [ 'h1', 'h2', 'p' ].indexOf( type ) ) {
		return type;
	}

	if ( 'huge' === fontSize || ( customFontSize && customFontSize > H1_FONT_SIZE ) ) {
		return 'h1';
	}

	if ( 'large' === fontSize || ( customFontSize && customFontSize > H2_FONT_SIZE ) ) {
		return 'h2';
	}

	const textLength = count( attributes.content, wordCountType, {} );

	if ( H1_TEXT_LENGTH >= textLength ) {
		return 'h1';
	}

	if ( H2_TEXT_LENGTH >= textLength ) {
		return 'h2';
	}

	return 'p';
}
