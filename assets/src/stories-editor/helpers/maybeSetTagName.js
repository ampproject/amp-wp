/**
 * WordPress dependencies
 */
import { count } from '@wordpress/wordcount';
import { _x } from '@wordpress/i18n';
import { dispatch, select } from '@wordpress/data';

const {
	getBlocksByClientId,
	getBlockOrder,
	getBlock,
} = select( 'core/block-editor' );

const { updateBlockAttributes } = dispatch( 'core/block-editor' );

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
 * @param {Object}  attributes                Block attributes.
 * @param {?string} attributes.fontSize       Font size slug.
 * @param {?number} attributes.customFontSize Custom font size in pixels.
 * @param {?number} attributes.positionTop    The block's top offset.
 * @param {?string} attributes.type           Text type. Can be one of 'auto', 'p', 'h1', or 'h2'.
 * @param {?string} attributes.content        Block content.
 * @param {boolean} canUseH1                  Whether an H1 tag is allowed. Prevents having multiple h1 tags on a page.
 *
 * @return {string} HTML tag name. Either p, h1, or h2.
 */
export const getTagName = ( attributes, canUseH1 = true ) => {
	const { fontSize, customFontSize, positionTop, type, content = '' } = attributes;

	if ( type && 'auto' !== type ) {
		return type;
	}

	// Elements positioned that low on a page are unlikely to be headings.
	if ( positionTop > 80 ) {
		return 'p';
	}

	if ( 'huge' === fontSize || ( customFontSize && customFontSize > H1_FONT_SIZE ) ) {
		return canUseH1 ? 'h1' : 'h2';
	}

	if ( 'large' === fontSize || ( customFontSize && customFontSize > H2_FONT_SIZE ) ) {
		return 'h2';
	}

	const textLength = count( content, wordCountType, {} );

	if ( H1_TEXT_LENGTH >= textLength ) {
		return canUseH1 ? 'h1' : 'h2';
	}

	if ( H2_TEXT_LENGTH >= textLength ) {
		return 'h2';
	}

	return 'p';
};

/**
 * Determines the HTML tag name that should be used for text blocks.
 *
 * This is based on the block's attributes, as well as the surrounding context.
 *
 * For example, there can only be one <h1> tag on a page.
 * Also, font size takes precedence over text length as it's a stronger signal for semantic meaning.
 *
 * @param {string} clientId Block ID.
 */
const maybeSetTagName = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block || 'amp/amp-story-text' !== block.name ) {
		return;
	}

	const siblings = getBlocksByClientId( getBlockOrder( clientId ) ).filter( ( { clientId: blockId } ) => blockId !== clientId );
	const canUseH1 = ! siblings.some( ( { attributes } ) => attributes.tagName === 'h1' );

	const tagName = getTagName( block.attributes, canUseH1 );

	if ( block.attributes.tagName !== tagName ) {
		updateBlockAttributes( clientId, { tagName } );
	}
};

export default maybeSetTagName;
