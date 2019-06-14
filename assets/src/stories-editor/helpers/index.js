/**
 * External dependencies
 */
import uuid from 'uuid/v4';
import classnames from 'classnames';
import { every, isEqual, has } from 'lodash';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { count } from '@wordpress/wordcount';
import { __, _x, sprintf } from '@wordpress/i18n';
import { select, dispatch } from '@wordpress/data';
import { createBlock, getBlockAttributes } from '@wordpress/blocks';
import { getColorClassName, getColorObjectByAttributeValues, getFontSize } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import '../store';
import {
	BlockNavigation,
	EditorCarousel,
	StoryControls,
	Shortcuts,
	withMetaBlockEdit,
	withMetaBlockSave,
	Inserter,
} from '../components';
import {
	ALLOWED_CHILD_BLOCKS,
	ALLOWED_MOVABLE_BLOCKS,
	ALLOWED_TOP_LEVEL_BLOCKS,
	STORY_PAGE_INNER_WIDTH,
	STORY_PAGE_INNER_HEIGHT,
	MEDIA_INNER_BLOCKS,
	BLOCKS_WITH_RESIZING,
	BLOCKS_WITH_TEXT_SETTINGS,
	MEGABYTE_IN_BYTES,
	VIDEO_ALLOWED_MEGABYTES_PER_SECOND,
	TEXT_BLOCK_BORDER,
} from '../constants';
import {
	MAX_FONT_SIZE,
	MIN_FONT_SIZE,
} from '../../common/constants';
import { getMinimumFeaturedImageDimensions, getBackgroundColorWithOpacity } from '../../common/helpers';

const { ampStoriesFonts } = window;

const {
	getBlocksByClientId,
	getBlockRootClientId,
	getBlockOrder,
	getBlock,
	getClientIdsWithDescendants,
} = select( 'core/block-editor' );

const {
	addAnimation,
	changeAnimationType,
	changeAnimationDuration,
	changeAnimationDelay,
} = dispatch( 'amp/story' );

const {
	getAnimatedBlocks,
} = select( 'amp/story' );

const {
	updateBlockAttributes,
} = dispatch( 'core/block-editor' );

/**
 * Adds a <link> element to the <head> for a given font in case there is none yet.
 *
 * Allows dynamically enqueuing font styles when needed.
 *
 * @param {string} name Font name.
 */
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
 * Filter block properties to define the parent block.
 *
 * @param {Object} props      Block properties.
 * @param {string} props.name Block name.
 *
 * @return {Object} Updated properties.
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
 * Returns the minimum height for a given block.
 *
 * @param {string} name Block name.
 *
 * @return {number} Block height in pixels.
 */
const getDefaultMinimumBlockHeight = ( name ) => {
	switch ( name ) {
		case 'core/quote':
		case 'core/video':
		case 'core/embed':
			return 200;

		case 'core/pullquote':
			return 250;

		case 'core/table':
			return 100;

		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			return 50;

		case 'amp/amp-story-post-title':
			return 100;

		default:
			return 60;
	}
};

/**
 * Adds AMP attributes to every allowed AMP Story block.
 *
 * @param {Object} settings Settings.
 * @param {string} name     Block name.
 *
 * @return {Object} Settings.
 */
export const addAMPAttributes = ( settings, name ) => {
	const isChildBlock = ALLOWED_CHILD_BLOCKS.includes( name );

	if ( ! isChildBlock || 'core/template' === name ) {
		return settings;
	}

	const isImageBlock = 'core/image' === name;
	const isVideoBlock = 'core/video' === name;

	const isMovableBlock = ALLOWED_MOVABLE_BLOCKS.includes( name );
	const needsTextSettings = BLOCKS_WITH_TEXT_SETTINGS.includes( name );
	// Image block already has width and heigh.
	const needsWidthHeight = BLOCKS_WITH_RESIZING.includes( name ) && ! isImageBlock;

	const addedAttributes = {
		anchor: {
			type: 'string',
			source: 'attribute',
			attribute: 'id',
			selector: 'amp-story-grid-layer .amp-story-block-wrapper > *, amp-story-cta-layer',
		},
		ampAnimationType: {
			type: 'string',
		},
		addedAttributes: {
			type: 'number',
			default: 0,
		},
		ampAnimationAfter: {
			type: 'string',
		},
		fontSize: {
			type: 'string',
		},
		customFontSize: {
			type: 'number',
		},
		ampFontFamily: {
			type: 'string',
		},
		textColor: {
			type: 'string',
		},
		customTextColor: {
			type: 'string',
		},
		backgroundColor: {
			type: 'string',
		},
		customBackgroundColor: {
			type: 'string',
		},
		opacity: {
			type: 'number',
			default: 100,
		},
	};

	if ( needsTextSettings ) {
		addedAttributes.autoFontSize = {
			type: 'number',
			default: 36,
		};
		addedAttributes.ampFitText = {
			type: 'boolean',
			default: true,
		};
	}

	if ( isMovableBlock ) {
		addedAttributes.positionTop = {
			default: 0,
			type: 'number',
		};

		addedAttributes.positionLeft = {
			default: 5,
			type: 'number',
		};

		if ( needsWidthHeight ) {
			addedAttributes.height = {
				type: 'number',
				default: getDefaultMinimumBlockHeight( name ),
			};

			addedAttributes.width = {
				type: 'number',
				default: 250,
			};
		}

		addedAttributes.rotationAngle = {
			type: 'number',
			default: 0,
		};

		addedAttributes.ampAnimationType = {
			source: 'attribute',
			selector: '.amp-story-block-wrapper',
			attribute: 'animate-in',
		};
		addedAttributes.ampAnimationDelay = {
			source: 'attribute',
			selector: '.amp-story-block-wrapper',
			attribute: 'animate-in-delay',
			default: 0,
		};
		addedAttributes.ampAnimationDuration = {
			source: 'attribute',
			selector: '.amp-story-block-wrapper',
			attribute: 'animate-in-duration',
			default: 0,
		};
		addedAttributes.ampAnimationAfter = {
			source: 'attribute',
			selector: '.amp-story-block-wrapper',
			attribute: 'animate-in-after',
		};
	}

	if ( isImageBlock ) {
		addedAttributes.ampShowImageCaption = {
			type: 'boolean',
			default: false,
		};
	}

	if ( isVideoBlock ) {
		// Required defaults for AMP validity.
		addedAttributes.autoplay = {
			...settings.attributes.autoplay,
			default: true,
		};
		addedAttributes.playsInline = {
			...settings.attributes.playsInline,
			default: false,
		};

		// Optional defaults.
		addedAttributes.loop = {
			...settings.attributes.loop,
			default: true,
		};
		addedAttributes.controls = {
			...settings.attributes.controls,
			default: false,
		};
	}

	// Keep default values of possibly already existing default values.
	Object.keys( addedAttributes ).forEach( ( attribute ) => {
		if ( 'undefined' !== typeof addedAttributes[ attribute ].default ) {
			return;
		}

		if ( 'undefined' !== typeof settings.attributes[ attribute ] && 'undefined' !== typeof settings.attributes[ attribute ].default ) {
			addedAttributes[ attribute ].default = settings.attributes[ attribute ].default;
		}
	} );

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
 * Filters block transformations.
 *
 * Removes prefixed list transformations to prevent automatic transformation.
 *
 * Adds a custom transform for blocks within <amp-story-grid-layer>.
 *
 * @see https://github.com/ampproject/amp-wp/issues/2370
 *
 * @param {Object} settings Settings.
 * @param {string} name     Block name.
 *
 * @return {Object} Settings.
 */
export const filterBlockTransforms = ( settings, name ) => {
	const isMovableBlock = ALLOWED_MOVABLE_BLOCKS.includes( name );

	if ( ! isMovableBlock ) {
		return settings;
	}

	const gridWrapperTransform = {
		type: 'raw',
		priority: 20,
		selector: `amp-story-grid-layer[data-block-name="${ name }"]`,
		transform: ( node ) => {
			const innerHTML = node.outerHTML;
			const blockAttributes = getBlockAttributes( name, innerHTML );

			if ( 'amp/amp-story-text' === name ) {
				/*
				 * When there is nothing that matches the content selector (.amp-text-content),
				 * the pasted content lacks the amp-fit-text wrapper and thus ampFitText is false.
				 */
				if ( ! blockAttributes.content ) {
					blockAttributes.content = node.textContent;
					blockAttributes.tagName = node.nodeName;
					blockAttributes.ampFitText = false;
				}
			}

			return createBlock( name, blockAttributes );
		},
	};

	const transforms = settings.transforms ? { ...settings.transforms } : {};
	let fromTransforms = transforms.from ? [ ...transforms.from ] : [];

	if ( 'core/list' === name ) {
		fromTransforms = fromTransforms.filter( ( { type } ) => 'prefix' !== type );
	}

	fromTransforms.push( gridWrapperTransform );

	return {
		...settings,
		transforms: {
			...transforms,
			from: fromTransforms,
		},
	};
};

/**
 * Add extra attributes to save to DB.
 *
 * @param {Object} props           Properties.
 * @param {Object} blockType       Block type object.
 * @param {string} blockType.name  Block type name.
 * @param {Object} attributes      Attributes.
 *
 * @return {Object} Props.
 */
export const addAMPExtraProps = ( props, blockType, attributes ) => {
	const ampAttributes = {};

	if ( ! ALLOWED_CHILD_BLOCKS.includes( blockType.name ) ) {
		return props;
	}

	const newProps = { ...props };

	// Always add anchor ID regardless of block support. Needed for animations.
	newProps.id = attributes.anchor || getUniqueId();

	if ( attributes.rotationAngle ) {
		let style = ! newProps.style ? {} : newProps.style;
		style = {
			...style,
			transform: `rotate(${ parseInt( attributes.rotationAngle ) }deg)`,
		};
		ampAttributes.style = style;
	}

	if ( attributes.ampFontFamily ) {
		ampAttributes[ 'data-font-family' ] = attributes.ampFontFamily;
	}

	return {
		...newProps,
		...ampAttributes,
	};
};

const blockContentDiv = document.createElement( 'div' );

/**
 * Filters block attributes to make sure that the className is taken even though it's wrapped in a grid layer.
 *
 * @param {Object} blockAttributes Block attributes.
 * @param {Object} blockType       Block type object. Unused.
 * @param {string} innerHTML       Inner HTML from saved content.
 *
 * @return {Object} Block attributes.
 */
export const filterBlockAttributes = ( blockAttributes, blockType, innerHTML ) => {
	if ( ! blockAttributes.className && innerHTML.includes( 'is-style-' ) && 0 === innerHTML.indexOf( '<amp-story-grid-layer' ) ) {
		blockContentDiv.innerHTML = innerHTML;

		// Lets check the first child of the amp-story-grid-layer for the className.
		if (
			blockContentDiv.children[ 0 ].children.length &&
			blockContentDiv.children[ 0 ].children[ 0 ].children.length &&
			blockContentDiv.children[ 0 ].children[ 0 ].children[ 0 ].className.includes( 'is-style-' )
		) {
			blockAttributes.className = blockContentDiv.children[ 0 ].children[ 0 ].children[ 0 ].className;
		}
	}

	return blockAttributes;
};

/**
 * Wraps all movable blocks in a grid layer and assigns custom attributes as needed.
 *
 * @param {WPElement} element                  Block element.
 * @param {Object}    blockType                Block type object.
 * @param {Object}    attributes               Block attributes.
 * @param {number}    attributes.positionTop   Top offset in pixel.
 * @param {number}    attributes.positionLeft  Left offset in pixel.
 * @param {number}    attributes.rotationAngle Rotation angle in degrees.
 * @param {number}    attributes.width         Block width in pixels.
 * @param {number}    attributes.height        Block height in pixels.
 *
 * @return {Object} The wrapped element.
 */
export const wrapBlocksInGridLayer = ( element, blockType, attributes ) => {
	if ( ! element || ! ALLOWED_MOVABLE_BLOCKS.includes( blockType.name ) ) {
		return element;
	}

	const {
		ampAnimationType,
		ampAnimationDelay,
		ampAnimationDuration,
		ampAnimationAfter,
		positionTop,
		positionLeft,
		width,
		height,
	} = attributes;

	let style = {};

	if ( 'undefined' !== typeof positionTop && 'undefined' !== typeof positionLeft ) {
		style = {
			...style,
			position: 'absolute',
			top: `${ positionTop || 0 }%`,
			left: `${ positionLeft || 0 }%`,
		};
	}

	// If the block has width and height set, set responsive values. Exclude text blocks since these already have it handled.
	if ( 'undefined' !== typeof width && 'undefined' !== typeof height ) {
		style = {
			...style,
			width: width ? `${ getPercentageFromPixels( 'x', width ) }%` : '0%',
			height: height ? `${ getPercentageFromPixels( 'y', height ) }%` : '0%',
		};
	}

	const animationAtts = {};

	// Add animation if necessary.
	if ( ampAnimationType ) {
		animationAtts[ 'animate-in' ] = ampAnimationType;

		if ( ampAnimationDelay ) {
			animationAtts[ 'animate-in-delay' ] = parseInt( ampAnimationDelay ) + 'ms';
		}

		if ( ampAnimationDuration ) {
			animationAtts[ 'animate-in-duration' ] = parseInt( ampAnimationDuration ) + 'ms';
		}

		if ( ampAnimationAfter ) {
			animationAtts[ 'animate-in-after' ] = ampAnimationAfter;
		}
	}

	return (
		<amp-story-grid-layer template="vertical" data-block-name={ blockType.name }>
			<div className="amp-story-block-wrapper" style={ style } { ...animationAtts }>
				{ element }
			</div>
		</amp-story-grid-layer>
	);
};

/**
 * Given a list of animated blocks, calculates the total duration
 * of all animations based on the durations and the delays.
 *
 * @param {Object[]} animatedBlocks               List of animated blocks.
 * @param {string} animatedBlocks[].id            The block's client ID.
 * @param {string} animatedBlocks[].parent        The block's parent client ID.
 * @param {string} animatedBlocks[].animationType The block's animation type.
 * @param {string} animatedBlocks[].duration      The block's animation duration.
 * @param {string} animatedBlocks[].delay         The block's animation delay.
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
		blockNavigation.id = 'amp-story-block-navigation';

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

		if ( ! document.getElementById( 'amp-story-controls' ) ) {
			const storyControls = document.createElement( 'div' );
			storyControls.id = 'amp-story-controls';

			ampStoryWrapper.appendChild( storyControls );

			render(
				<StoryControls />,
				storyControls
			);
		}

		ampStoryWrapper.appendChild( editorBlockList );

		ampStoryWrapper.appendChild( blockNavigation );

		render(
			<BlockNavigation />,
			blockNavigation
		);

		if ( ! document.getElementById( 'amp-story-editor-carousel' ) ) {
			const editorCarousel = document.createElement( 'div' );
			editorCarousel.id = 'amp-story-editor-carousel';

			ampStoryWrapper.appendChild( editorCarousel );

			render(
				<EditorCarousel />,
				editorCarousel
			);
		}
	}

	if ( editorBlockNavigation ) {
		if ( ! document.getElementById( 'amp-story-shortcuts' ) ) {
			const shortcuts = document.createElement( 'div' );
			shortcuts.id = 'amp-story-shortcuts';

			editorBlockNavigation.parentNode.parentNode.insertBefore( shortcuts, editorBlockNavigation.parentNode.nextSibling );

			render(
				<Shortcuts />,
				shortcuts
			);
		}

		const customInserter = document.createElement( 'div' );
		customInserter.id = 'amp-story-inserter';

		const inserterWrapper = editorBlockNavigation.parentNode.parentNode.querySelector( '.block-editor-inserter' ).parentNode;
		inserterWrapper.parentNode.replaceChild( customInserter, inserterWrapper );

		render(
			<Inserter position="bottom right" />,
			customInserter
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
 * Calculates font size that fits to the text element based on the element's size.
 * Replicates amp-fit-text's logic in the editor.
 *
 * @see https://github.com/ampproject/amphtml/blob/e7a1b3ff97645ec0ec482192205134bd0735943c/extensions/amp-fit-text/0.1/amp-fit-text.js
 *
 * @param {Object} measurer       HTML element.
 * @param {number} expectedHeight Maximum height.
 * @param {number} expectedWidth  Maximum width.
 * @param {number} maxFontSize    Maximum font size.
 * @param {number} minFontSize    Minimum font size.
 *
 * @return {number|boolean} Calculated font size. False if calculation wasn't possible.
 */
export const calculateFontSize = ( measurer, expectedHeight, expectedWidth, maxFontSize, minFontSize ) => {
	// Return false if calculation is not possible due to width and height missing, e.g. in disabled preview.
	if ( ! measurer.offsetHeight || ! measurer.offsetWidth ) {
		return false;
	}
	measurer.classList.toggle( 'is-measuring-fontsize' );

	maxFontSize++;

	// Binomial search for the best font size.
	while ( maxFontSize - minFontSize > 1 ) {
		const mid = Math.floor( ( minFontSize + maxFontSize ) / 2 );
		measurer.style.fontSize = mid + 'px';
		const currentHeight = measurer.offsetHeight;
		const currentWidth = measurer.offsetWidth;
		if ( currentHeight > expectedHeight || currentWidth > expectedWidth ) {
			maxFontSize = mid;
		} else {
			minFontSize = mid;
		}
	}

	// Let's restore the correct font size, too.
	measurer.style.fontSize = minFontSize + 'px';

	measurer.classList.toggle( 'is-measuring-fontsize' );

	return minFontSize;
};

/**
 * Get percentage of a distance compared to the full width / height of the page.
 *
 * @param {string} axis       X or Y axis.
 * @param {number} pixelValue Value in pixels.
 *
 * @return {number} Value in percentage.
 */
export const getPercentageFromPixels = ( axis, pixelValue ) => {
	if ( 'x' === axis ) {
		return Number( ( ( pixelValue / STORY_PAGE_INNER_WIDTH ) * 100 ).toFixed( 2 ) );
	} else if ( 'y' === axis ) {
		return Number( ( ( pixelValue / STORY_PAGE_INNER_HEIGHT ) * 100 ).toFixed( 2 ) );
	}
	return 0;
};

/**
 * Get pixel value from percentage, based on the full width / height of the page.
 *
 * @param {string} axis            X or Y axis.
 * @param {number} percentageValue Value in percent.
 *
 * @return {number} Value in percentage.
 */
export const getPixelsFromPercentage = ( axis, percentageValue ) => {
	if ( 'x' === axis ) {
		return Math.round( ( percentageValue / 100 ) * STORY_PAGE_INNER_WIDTH );
	} else if ( 'y' === axis ) {
		return Math.round( ( percentageValue / 100 ) * STORY_PAGE_INNER_HEIGHT );
	}
	return 0;
};

/**
 * Returns the minimum dimensions for a story poster image.
 *
 * @link https://www.ampproject.org/docs/reference/components/amp-story#poster-guidelines-(for-poster-portrait-src,-poster-landscape-src,-and-poster-square-src)
 *
 * @return {Object} Minimum dimensions including width and height.
 */
export const getMinimumStoryPosterDimensions = () => {
	const posterImageWidth = 696;
	const posterImageHeight = 928;

	const expectedAspectRatio = posterImageWidth / posterImageHeight;

	const { width: featuredImageWidth } = getMinimumFeaturedImageDimensions();

	const width = Math.max( posterImageWidth, featuredImageWidth );

	// Adjust the height to make sure the aspect ratio of the poster image is preserved.
	return {
		width,
		height: ( 1 / expectedAspectRatio ) * width,
	};
};

/**
 * Adds either background color or gradient to style depending on the settings.
 *
 * @param {Object} overlayStyle     Original style.
 * @param {Array}  backgroundColors Array of color settings.
 *
 * @return {Object} Adjusted style.
 */
export const addBackgroundColorToOverlay = ( overlayStyle, backgroundColors ) => {
	const validBackgroundColors = backgroundColors.filter( Boolean );

	if ( ! validBackgroundColors ) {
		return overlayStyle;
	}

	if ( 1 === validBackgroundColors.length ) {
		overlayStyle.backgroundColor = validBackgroundColors[ 0 ].color;
	} else {
		const gradientList = validBackgroundColors.map( ( { color } ) => {
			return color || 'transparent';
		} ).join( ', ' );

		overlayStyle.backgroundImage = `linear-gradient(to bottom, ${ gradientList })`;
	}
	return overlayStyle;
};

/**
 * Resets a block's attributes except for a few ones relevant for the layout.
 *
 * @param {Object} block Block object.
 * @param {Object} block.attributes Block attributes.
 *
 * @return {Object} Filtered block attributes.
 */
const resetBlockAttributes = ( block ) => {
	const attributes = {};
	const attributesToKeep = [ 'positionTop', 'positionLeft', 'width', 'height', 'tagName', 'align', 'content', 'text', 'value', 'citation', 'autoFontSize', 'rotationAngle' ];

	for ( const key in block.attributes ) {
		if ( block.attributes.hasOwnProperty( key ) && attributesToKeep.includes( key ) ) {
			attributes[ key ] = block.attributes[ key ];
		}
	}

	return attributes;
};

/**
 * Creates a skeleton template from pre-populated template.
 *
 * Basically resets all block attributes back to their defaults.
 *
 * @param {Object}   template             Template block object.
 * @param {string}   template.name        Block name.
 * @param {Object[]} template.innerBlocks List of inner blocks.
 *
 * @return {Object} Skeleton template block.
 */
export const createSkeletonTemplate = ( template ) => {
	const innerBlocks = [];

	for ( const innerBlock of template.innerBlocks ) {
		innerBlocks.push( createBlock( innerBlock.name, resetBlockAttributes( innerBlock ) ) );
	}

	return createBlock( template.name, resetBlockAttributes( template ), innerBlocks );
};

/**
 * Determines a block's HTML class name based on its attributes.
 *
 * @param {string[]} className             List of pre-existing class names for the block.
 * @param {boolean}  ampFitText            Whether amp-fit-text should be used or not.
 * @param {?string}  backgroundColor       A string containing the background color slug.
 * @param {?string}  textColor             A string containing the color slug.
 * @param {string}   customBackgroundColor A string containing the custom background color value.
 * @param {string}   customTextColor       A string containing the custom color value.
 * @param {?number}  opacity               Opacity.
 *
 * @return {string} The block's HTML class name.
 */
export const getClassNameFromBlockAttributes = ( {
	className,
	ampFitText,
	backgroundColor,
	textColor,
	customBackgroundColor,
	customTextColor,
	opacity,
} ) => {
	const textClass = getColorClassName( 'color', textColor );
	const backgroundClass = getColorClassName( 'background-color', backgroundColor );

	const hasOpacity = opacity && opacity < 100;

	return classnames( className, {
		'amp-text-content': ! ampFitText,
		'has-text-color': textColor || customTextColor,
		'has-background': backgroundColor || customBackgroundColor,
		[ textClass ]: textClass,
		[ backgroundClass ]: ! hasOpacity ? backgroundClass : undefined,
	} );
};

/**
 * Determines a block's inline style based on its attributes.
 *
 * @param {string}  align                 Block alignment.
 * @param {?string} fontSize              Font size slug.
 * @param {?number} customFontSize        Custom font size in pixels.
 * @param {boolean} ampFitText            Whether amp-fit-text should be used or not.
 * @param {?string} backgroundColor       A string containing the background color slug.
 * @param {?string} textColor             A string containing the color slug.
 * @param {string}  customBackgroundColor A string containing the custom background color value.
 * @param {string}  customTextColor       A string containing the custom color value.
 * @param {?number} opacity               Opacity.
 *
 * @return {Object} Block inline style.
 */
export const getStylesFromBlockAttributes = ( {
	align,
	fontSize,
	customFontSize,
	ampFitText,
	backgroundColor,
	textColor,
	customBackgroundColor,
	customTextColor,
	opacity,
} ) => {
	const textClass = getColorClassName( 'color', textColor );

	const { colors, fontSizes } = select( 'core/block-editor' ).getSettings();

	/*
     * Calculate font size using vw to make it responsive.
     *
     * Get the font size in px based on the slug with fallback to customFontSize.
     */
	const userFontSize = fontSize ? getFontSize( fontSizes, fontSize, customFontSize ).size : customFontSize;
	const fontSizeResponsive = userFontSize && ( ( userFontSize / STORY_PAGE_INNER_WIDTH ) * 100 ).toFixed( 2 ) + 'vw';

	const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, getColorObjectByAttributeValues( colors, backgroundColor, customBackgroundColor ), customBackgroundColor, opacity );

	return {
		backgroundColor: appliedBackgroundColor,
		color: textClass ? undefined : customTextColor,
		fontSize: ! ampFitText ? fontSizeResponsive : undefined,
		textAlign: align,
	};
};

/**
 * Returns the settings object for the AMP story meta blocks (post title, author, date).
 *
 * @param {string}  attribute   The post attribute this meta block reads from.
 * @param {?string} placeholder Optional. Placeholder text in case the attribute is empty.
 * @param {string}  tagName     Optional. The HTML tag name to use for the content. Default '<p>'.
 * @param {boolean} isEditable  Optional. Whether the meta block is editable by the user or not. Default false.
 *
 * @return {Object} The meta block's settings object.
 */
export const getMetaBlockSettings = ( { attribute, placeholder, tagName = 'p', isEditable = false } ) => {
	const supports = {
		anchor: true,
		reusable: true,
	};

	const schema = {
		align: {
			type: 'string',
		},
	};

	return {
		supports,
		attributes: schema,
		save: withMetaBlockSave( { tagName } ),
		edit: withMetaBlockEdit( { attribute, placeholder, tagName, isEditable } ),
	};
};

/**
 * Removes a pre-set caption from image block.
 *
 * @param {string} clientId Block ID.
 */
export const maybeRemoveImageCaption = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block || 'core/image' !== block.name ) {
		return;
	}

	const { attributes } = block;

	// If we have an image with pre-set caption we should remove the caption.
	if ( ! attributes.ampShowImageCaption && attributes.caption && 0 !== attributes.caption.length ) {
		updateBlockAttributes( clientId, { caption: '' } );
	}
};

/**
 * Set initial positioning if the selected block is an unmodified block.
 *
 * @param {string} clientId Block ID.
 */
export const maybeSetInitialPositioning = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block || ! ALLOWED_CHILD_BLOCKS.includes( block.name ) ) {
		return;
	}

	const parentBlock = getBlock( getBlockRootClientId( clientId ) );
	// Short circuit if the top position is already set or the block has no parent.
	if ( 0 !== block.attributes.positionTop || ! parentBlock ) {
		return;
	}

	const positionTopLimit = 75;
	const positionTopHighest = 0;
	const positionTopGap = 10;

	// Check if it's a new block.
	const newBlock = createBlock( block.name );
	const isUnmodified = every( newBlock.attributes, ( value, key ) => value === block.attributes[ key ] );

	// Only set the position if the block was unmodified before.
	if ( isUnmodified ) {
		const highestPositionTop = parentBlock.innerBlocks
			.map( ( childBlock ) => childBlock.attributes.positionTop )
			.reduce( ( highestTop, positionTop ) => Math.max( highestTop, positionTop ), 0 );

		// If it's more than the limit, set the new one.
		const newPositionTop = highestPositionTop > positionTopLimit ? positionTopHighest : highestPositionTop + positionTopGap;

		updateBlockAttributes( clientId, { positionTop: newPositionTop } );
	}
};

/**
 * Verify and perhaps update autoAdvanceAfterMedia attribute for pages.
 *
 * For pages with autoAdvanceAfter set to 'media',
 * verify that the referenced media block still exists.
 * If not, find another media block to be used for the
 * autoAdvanceAfterMedia attribute.
 *
 * @param {string} clientId Block ID.
 */
export const maybeUpdateAutoAdvanceAfterMedia = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block || ! ALLOWED_TOP_LEVEL_BLOCKS.includes( block.name ) ) {
		return;
	}

	if ( 'media' !== block.attributes.autoAdvanceAfter ) {
		return;
	}

	const innerBlocks = getBlocksByClientId( getBlockOrder( clientId ) );

	const mediaBlock = block.attributes.autoAdvanceAfterMedia && innerBlocks.find( ( { attributes } ) => attributes.anchor === block.attributes.autoAdvanceAfterMedia );

	if ( mediaBlock ) {
		return;
	}

	const firstMediaBlock = innerBlocks.find( ( { name } ) => MEDIA_INNER_BLOCKS.includes( name ) );
	const autoAdvanceAfterMedia = firstMediaBlock ? firstMediaBlock.attributes.anchor : '';

	if ( block.attributes.autoAdvanceAfterMedia !== autoAdvanceAfterMedia ) {
		updateBlockAttributes( clientId, { autoAdvanceAfterMedia } );
	}
};

/**
 * Returns a block's inner element containing the actual text node with its content.
 *
 * @param {Object} block Block object.
 *
 * @return {null|Element} The inner element.
 */
const getBlockInnerTextElement = ( block ) => {
	const { name, clientId } = block;

	switch ( name ) {
		case 'amp/amp-story-text':
			return document.querySelector( `#block-${ clientId } .block-editor-rich-text__editable.is-amp-fit-text` );

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const slug = name.replace( '/', '-' );
			return document.querySelector( `#block-${ clientId } .wp-block-${ slug }` );
	}

	return null;
};

/**
 * Updates a block's font size in case it uses amp-fit-text and the content has changed.
 *
 * @param {Object}  block                         Block object.
 * @param {string}  block.clientId                Block client ID.
 * @param {Object}  block.attributes              Block attributes.
 * @param {number}  block.attributes.width        Block width in pixels.
 * @param {number}  block.attributes.height       Block height in pixels.
 * @param {string}  block.attributes.content      Block inner content.
 * @param {boolean} block.attributes.ampFitText   Whether amp-fit-text should be used or not.
 * @param {number}  block.attributes.autoFontSize Automatically determined font size for amp-fit-text blocks.
 */
export const maybeUpdateFontSize = ( block ) => {
	const { name, clientId, attributes } = block;
	const { width, height, ampFitText, content, autoFontSize } = attributes;

	if ( ! ampFitText ) {
		return;
	}

	switch ( name ) {
		case 'amp/amp-story-text':
			const element = getBlockInnerTextElement( block );

			if ( element && content.length ) {
				const fitFontSize = calculateFontSize( element, height + TEXT_BLOCK_BORDER, width + TEXT_BLOCK_BORDER, MAX_FONT_SIZE, MIN_FONT_SIZE );

				if ( fitFontSize && autoFontSize !== fitFontSize ) {
					updateBlockAttributes( clientId, { autoFontSize: fitFontSize } );
				}
			}

			break;

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const metaBlockElement = getBlockInnerTextElement( block );

			if ( metaBlockElement ) {
				const fitFontSize = calculateFontSize( metaBlockElement, height, width, MAX_FONT_SIZE, MIN_FONT_SIZE );
				if ( fitFontSize && autoFontSize !== fitFontSize ) {
					updateBlockAttributes( clientId, { autoFontSize: fitFontSize } );
				}
			}

			break;
	}
};

/**
 * Sets width and height for blocks if they haven't been modified yet.
 *
 * @param {string} clientId Block ID.
 */
export const maybeSetInitialSize = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block ) {
		return;
	}

	const { name, attributes } = block;

	if ( 'core/image' !== name ) {
		return;
	}
	const { width, height } = attributes;

	/**
	 * Sets width and height to image if it hasn't been set via resizing yet.
	 *
	 * Takes the values from the original image.
	 */
	if ( ! width && ! height && attributes.id > 0 ) {
		const { getMedia } = select( 'core' );

		const media = getMedia( attributes.id );
		// If the width and height haven't been set for the media, we should get it from the original image.
		if ( media && media.media_details ) {
			const { height: imageHeight, width: imageWidth } = media.media_details;

			let ratio = 1;
			// If the image exceeds the page limits, adjust the width and height accordingly.
			if ( STORY_PAGE_INNER_WIDTH < imageWidth || STORY_PAGE_INNER_HEIGHT < imageHeight ) {
				ratio = Math.max( imageWidth / STORY_PAGE_INNER_WIDTH, imageHeight / STORY_PAGE_INNER_HEIGHT );
			}

			updateBlockAttributes( clientId, {
				width: Math.round( imageWidth / ratio ),
				height: Math.round( imageHeight / ratio ),
			} );
		}
	}
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
export const maybeSetTagName = ( clientId ) => {
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

/**
 * Initializes the animations if it hasn't been done yet.
 */
export const maybeInitializeAnimations = () => {
	const animations = getAnimatedBlocks();
	if ( isEqual( {}, animations ) ) {
		const allBlocks = getBlocksByClientId( getClientIdsWithDescendants() );
		for ( const block of allBlocks ) {
			const page = getBlockRootClientId( block.clientId );

			if ( page ) {
				const { ampAnimationType, ampAnimationAfter, ampAnimationDuration, ampAnimationDelay } = block.attributes;
				const predecessor = allBlocks.find( ( b ) => b.attributes.anchor === ampAnimationAfter );

				addAnimation( page, block.clientId, predecessor ? predecessor.clientId : undefined );

				changeAnimationType( page, block.clientId, ampAnimationType );
				changeAnimationDuration( page, block.clientId, ampAnimationDuration ? parseInt( ampAnimationDuration.replace( 'ms', '' ) ) : undefined );
				changeAnimationDelay( page, block.clientId, ampAnimationDelay ? parseInt( ampAnimationDelay.replace( 'ms', '' ) ) : undefined );
			}
		}
	}
};

/**
 * Get the distance between two points based on pythagorean.
 *
 * @param {number} deltaX Difference between X coordinates.
 * @param {number} deltaY Difference between Y coordinates.
 * @return {number} Difference between the two points.
 */
const getDelta = ( deltaX, deltaY ) => Math.sqrt( Math.pow( deltaX, 2 ) + Math.pow( deltaY, 2 ) );

/**
 * Converts degrees to radian.
 *
 * @param {number} angle Angle.
 * @return {number} Radian.
 */
export const getRadianFromDeg = ( angle ) => angle * Math.PI / 180;

/**
 * Gets width and height delta values based on the original coordinates, rotation angle and mouse event.
 *
 * @param {Object} event MouseEvent.
 * @param {number} angle Rotation angle.
 * @param {number} lastSeenX Starting X coordinate.
 * @param {number} lastSeenY Starint Y coordinate.
 * @param {string} direction Direction of resizing.
 * @return {Object} Width and height values.
 */
export const getResizedWidthAndHeight = ( event, angle, lastSeenX, lastSeenY, direction ) => {
	const deltaY = event.clientY - lastSeenY;
	const deltaX = event.clientX - lastSeenX;
	const deltaL = getDelta( deltaX, deltaY );

	// Get the angle between the two points.
	const alpha = Math.atan2( deltaY, deltaX );
	// Get the difference with rotation angle.
	const beta = alpha - getRadianFromDeg( angle );
	const deltaW = 'right' === direction ? deltaL * Math.cos( beta ) : 0;
	const deltaH = 'bottom' === direction ? deltaL * Math.sin( beta ) : 0;

	return {
		deltaW,
		deltaH,
	};
};

/**
 * Get block's left and top position based on width, height, and radian.
 *
 * @param {number} width Width.
 * @param {number} height Height.
 * @param {number} radian Radian.
 * @return {{top: number, left: number}} Top and left positioning.
 */
export const getBlockPositioning = ( width, height, radian ) => {
	const x = -width / 2;
	const y = height / 2;
	const rotatedX = ( y * Math.sin( radian ) ) + ( x * Math.cos( radian ) );
	const rotatedY = ( y * Math.cos( radian ) ) - ( x * Math.sin( radian ) );
	return {
		left: rotatedX - x,
		top: rotatedY - y,
	};
};

/**
 * Return a label for the block order controls depending on block position.
 *
 * @param {string}  type            Block type - in the case of a single block, should
 *                                  define its 'type'. I.e. 'Text', 'Heading', 'Image' etc.
 * @param {number}  currentPosition The block's current position.
 * @param {number}  newPosition     The block's new position.
 * @param {boolean} isFirst         This is the first block.
 * @param {boolean} isLast          This is the last block.
 * @param {number}  dir             Direction of movement (> 0 is considered to be going
 *                                  down, < 0 is up).
 *
 * @return {string} Label for the block movement controls.
 */
export const getBlockOrderDescription = ( type, currentPosition, newPosition, isFirst, isLast, dir ) => {
	if ( isFirst && isLast ) {
		// translators: %s: Type of block (i.e. Text, Image etc)
		return sprintf( __( 'Block %s is the only block, and cannot be moved', 'amp' ), type );
	}

	if ( dir > 0 && ! isLast ) {
		// moving down
		return sprintf(
			// translators: 1: Type of block (i.e. Text, Image etc), 2: Position of selected block, 3: New position
			__( 'Move %1$s block from position %2$d down to position %3$d', 'amp' ),
			type,
			currentPosition,
			newPosition
		);
	}

	if ( dir > 0 && isLast ) {
		// moving down, and is the last item
		// translators: %s: Type of block (i.e. Text, Image etc)
		return sprintf( __( 'Block %s is at the end of the content and can’t be moved down', 'amp' ), type );
	}

	if ( dir < 0 && ! isFirst ) {
		// moving up
		return sprintf(
			// translators: 1: Type of block (i.e. Text, Image etc), 2: Position of selected block, 3: New position
			__( 'Move %1$s block from position %2$d up to position %3$d', 'amp' ),
			type,
			currentPosition,
			newPosition
		);
	}

	if ( dir < 0 && isFirst ) {
		// moving up, and is the first item
		// translators: %s: Type of block (i.e. Text, Image etc)
		return sprintf( __( 'Block %s is at the beginning of the content and can’t be moved up', 'amp' ), type );
	}
};

/**
 * Get CTA block.
 *
 * @param {Array} pageClientId Root ID.
 * @return {Object} CTA block.
 */
export const getCallToActionBlock = ( pageClientId ) => {
	const innerBlocks = getBlocksByClientId( getBlockOrder( pageClientId ) );
	return innerBlocks.find( ( { name } ) => name === 'amp/amp-story-cta' );
};

/**
 * Gets the number of megabytes per second for the video.
 *
 * @param {Object} media The media object of the video.
 * @return {number|null} Number of megabytes per second, or null if media details unavailable.
 */
export const getVideoBytesPerSecond = ( media ) => {
	if ( ! has( media, [ 'media_details', 'filesize' ] ) || ! has( media, [ 'media_details', 'length' ] ) ) {
		return null;
	}
	return media.media_details.filesize / media.media_details.length;
};

/**
 * Gets whether the video file size is over a certain amount of MB per second.
 *
 * @param {Object} media The media object of the video.
 * @return {boolean} Whether the file size is more than a certain amount of MB per second, or null of the data isn't available.
 */
export const isVideoSizeExcessive = ( media ) => {
	if ( ! has( media, [ 'media_details', 'filesize' ] ) || ! has( media, [ 'media_details', 'length' ] ) ) {
		return false;
	}

	return media.media_details.filesize > media.media_details.length * VIDEO_ALLOWED_MEGABYTES_PER_SECOND * MEGABYTE_IN_BYTES;
};

/**
 * Returns a unique ID that is guaranteed to not start with a number.
 *
 * Useful for using in HTML attributes.
 *
 * @return {string} Unique ID.
 */
export const getUniqueId = () => {
	return uuid().replace( /^\d/, 'a' );
};
