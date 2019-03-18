/* global ampStoriesFonts */

/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { count } from '@wordpress/wordcount';
import { _x } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	BlockNavigation,
	EditorCarousel,
	StoryControls,
	Shortcuts,
} from './components';
import { ALLOWED_CHILD_BLOCKS, ALLOWED_TOP_LEVEL_BLOCKS, BLOCK_TAG_MAPPING } from './constants';

export const maybeEnqueueFontStyle = ( name ) => {
	if ( ! name || 'undefined' === typeof ampStoriesFonts ) {
		return;
	}

	const font = ampStoriesFonts.find( ( thisFont ) => thisFont.name === name );
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

/**
 * Filter layer properties to define the parent block.
 *
 * @param {Object} props Block properties.
 * @param {string} props.name Block name.
 * @return {Object} Properties.
 */
export const setBlockParent = ( props ) => {
	const { name } = props;

	if ( ! ALLOWED_TOP_LEVEL_BLOCKS.includes( name ) ) {
		// Only amp/amp-story-page blocks can be on the top level.
		return {
			...props,
			parent: [ 'amp/amp-story-page' ],
		};
	}

	if ( name !== 'amp/amp-story-page' ) {
		// Do not allow inserting any of the blocks if they're not AMP Story blocks.
		return {
			...props,
			parent: [ '' ],
		};
	}

	return props;
};

/**
 * Add AMP attributes to every allowed AMP Story block.
 *
 * @param {Object} settings Settings.
 * @param {string} name Block name.
 * @return {Object} Settings.
 */
export const addAMPAttributes = ( settings, name ) => {
	if ( ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
		return settings;
	}

	const addedAttributes = {
		anchor: {
			type: 'string',
			source: 'attribute',
			attribute: 'id',
			selector: '*',
		},
	};

	// Define selector according to mappings.
	if ( BLOCK_TAG_MAPPING[ name ] ) {
		addedAttributes.ampAnimationType = {
			source: 'attribute',
			selector: BLOCK_TAG_MAPPING[ name ],
			attribute: 'animate-in',
		};
		addedAttributes.ampAnimationDelay = {
			source: 'attribute',
			selector: BLOCK_TAG_MAPPING[ name ],
			attribute: 'animate-in-delay',
			default: 0,
		};
		addedAttributes.ampAnimationDuration = {
			source: 'attribute',
			selector: BLOCK_TAG_MAPPING[ name ],
			attribute: 'animate-in-duration',
			default: 0,
		};
		addedAttributes.ampAnimationAfter = {
			source: 'attribute',
			selector: BLOCK_TAG_MAPPING[ name ],
			attribute: 'animate-in-after',
		};
	} else if ( 'core/list' === name ) {
		addedAttributes.ampAnimationType = {
			type: 'string',
		};
		addedAttributes.ampAnimationDelay = {
			type: 'number',
			default: 0,
		};
		addedAttributes.ampAnimationDuration = {
			type: 'number',
			default: 0,
		};
		addedAttributes.ampAnimationAfter = {
			type: 'string',
		};
	}

	if ( 'core/image' === name ) {
		addedAttributes.ampShowImageCaption = {
			type: 'boolean',
			default: false,
		};
	}

	addedAttributes.positionTop = {
		type: 'number',
		default: 0,
	};
	addedAttributes.positionLeft = {
		type: 'number',
		default: 5,
	};

	return {
		...settings,
		attributes: {
			...settings.attributes,
			...addedAttributes,
		},
		supports: {
			...settings.supports,
			anchor: false,
		},
	};
};

/**
 * Add extra attributes to save to DB.
 *
 * @param {Object} props Properties.
 * @param {Object} blockType Block type.
 * @param {Object} attributes Attributes.
 * @return {Object} Props.
 */
export const addAMPExtraProps = ( props, blockType, attributes ) => {
	const ampAttributes = {};

	if ( ! ALLOWED_CHILD_BLOCKS.includes( blockType.name ) ) {
		return props;
	}

	const newProps = { ...props };

	// Always add anchor ID regardless of block support. Needed for animations.
	newProps.id = attributes.anchor || uuid();

	if ( attributes.ampAnimationType ) {
		ampAttributes[ 'animate-in' ] = attributes.ampAnimationType;

		if ( attributes.ampAnimationDelay ) {
			ampAttributes[ 'animate-in-delay' ] = attributes.ampAnimationDelay;
		}

		if ( attributes.ampAnimationDuration ) {
			ampAttributes[ 'animate-in-duration' ] = attributes.ampAnimationDuration;
		}

		if ( attributes.ampAnimationAfter ) {
			ampAttributes[ 'animate-in-after' ] = attributes.ampAnimationAfter;
		}
	}

	if ( attributes.ampFontFamily ) {
		ampAttributes[ 'data-font-family' ] = attributes.ampFontFamily;
	}

	if ( 'undefined' !== typeof attributes.positionTop && 'undefined' !== typeof attributes.positionLeft ) {
		const style = props.style ? props.style : {};
		const positionStyle = {
			position: 'absolute',
			top: `${ attributes.positionTop }%`,
			left: `${ attributes.positionLeft }%`,
		};
		ampAttributes.style = {
			...style,
			...positionStyle,
		};
	}

	return {
		...newProps,
		...ampAttributes,
	};
};
/**
 * Given a list of animated blocks, calculates the total duration
 * of all animations based on the durations and the delays.
 *
 * @param {Array} animatedBlocks List of animated blocks.
 *
 * @return {number} Total animation duration time.
 */
export const getTotalAnimationDuration = ( animatedBlocks ) => {
	const getLongestAnimation = ( parentBlockId ) => {
		return animatedBlocks
			.filter( ( { parent, animationType } ) => parent === parentBlockId && animationType )
			.map( ( { duration, delay } ) => {
				const animationDelay = delay ? parseInt( delay ) : 0;
				const animationDuration = duration ? parseInt( duration ) : 0;

				return animationDelay + animationDuration;
			} )
			.reduce( ( max, current ) => Math.max( max, current ), 0 );
	};

	const levels = [ ...new Set( animatedBlocks.map( ( { parent } ) => parent ) ) ];

	return levels.map( getLongestAnimation ).reduce( ( sum, duration ) => sum + duration, 0 );
};

/**
 * Add some additional elements needed to render our custom UI controls.
 */
export const renderStoryComponents = () => {
	const editorBlockList = document.querySelector( '.editor-block-list__layout' );
	const editorBlockNavigation = document.querySelector( '.editor-block-navigation' );

	if ( editorBlockList ) {
		const ampStoryWrapper = document.createElement( 'div' );
		ampStoryWrapper.id = 'amp-story-editor';

		const blockNavigation = document.createElement( 'div' );
		blockNavigation.id = 'amp-root-navigation';

		const editorCarousel = document.createElement( 'div' );
		editorCarousel.id = 'amp-story-editor-carousel';

		const storyControls = document.createElement( 'div' );
		storyControls.id = 'amp-story-controls';

		/**
		 * The intended layout is as follows:
		 *
		 * - Post title
		 * - AMP story wrapper element (needed for overflow styling)
		 * - - Story controls
		 * - - Block list
		 * - - Block navigation
		 * - - Carousel controls
		 */
		editorBlockList.parentNode.replaceChild( ampStoryWrapper, editorBlockList );
		ampStoryWrapper.appendChild( storyControls );
		ampStoryWrapper.appendChild( editorBlockList );
		ampStoryWrapper.appendChild( blockNavigation );
		ampStoryWrapper.appendChild( editorCarousel );

		render(
			<StoryControls />,
			storyControls
		);

		render(
			<div key="blockNavigation" className="block-navigation">
				<BlockNavigation />
			</div>,
			blockNavigation
		);

		render(
			<div key="pagesCarousel" className="editor-carousel">
				<EditorCarousel />
			</div>,
			editorCarousel
		);
	}

	if ( editorBlockNavigation ) {
		const shortcuts = document.createElement( 'div' );
		shortcuts.id = 'amp-story-shortcuts';

		editorBlockNavigation.parentNode.parentNode.insertBefore( shortcuts, editorBlockNavigation.parentNode.nextSibling );

		render(
			<Shortcuts />,
			shortcuts
		);
	}
};

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
 * @param {Object}  attributes Block attributes.
 * @param {boolean} canUseH1   Whether an H1 tag is allowed.
 *
 * @return {string} HTML tag name. Either p, h1, or h2.
 */
export const getTagName = ( attributes, canUseH1 ) => {
	const { fontSize, customFontSize, positionTop } = attributes;

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

	const textLength = count( attributes.content, wordCountType, {} );

	if ( H1_TEXT_LENGTH >= textLength ) {
		return canUseH1 ? 'h1' : 'h2';
	}

	if ( H2_TEXT_LENGTH >= textLength ) {
		return 'h2';
	}

	return 'p';
};
