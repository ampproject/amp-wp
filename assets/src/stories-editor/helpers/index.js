/**
 * External dependencies
 */
import uuid from 'uuid/v4';
import classnames from 'classnames';
import { each, every, isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { count } from '@wordpress/wordcount';
import { _x } from '@wordpress/i18n';
import { select, dispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { getColorClassName, RichText } from '@wordpress/block-editor';

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
	Inserter,
} from '../../components';
import {
	ALLOWED_CHILD_BLOCKS,
	ALLOWED_MOVABLE_BLOCKS,
	ALLOWED_TOP_LEVEL_BLOCKS,
	BLOCK_TAG_MAPPING,
	STORY_PAGE_INNER_WIDTH,
	STORY_PAGE_INNER_HEIGHT,
	MEDIA_INNER_BLOCKS,
} from '../constants';
import { getMinimumFeaturedImageDimensions, getBackgroundColorWithOpacity } from '../../common/helpers';
import ampStoriesFonts from 'amp-stories-fonts';

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

const { updateBlockAttributes } = dispatch( 'core/block-editor' );

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

		case 'core/image':
			return 215;

		case 'core/pullquote':
			return 215;

		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			return 30;

		default:
			return 50;
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

	if ( ! isChildBlock ) {
		return settings;
	}

	const isImageBlock = 'core/image' === name;
	const isMovableBlock = ALLOWED_MOVABLE_BLOCKS.includes( name );

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

	if ( isMovableBlock ) {
		addedAttributes.positionTop = {
			type: 'number',
			default: 0,
		};

		addedAttributes.positionLeft = {
			type: 'number',
			default: 5,
		};

		if ( ! isImageBlock ) {
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
	}

	if ( isImageBlock ) {
		addedAttributes.ampShowImageCaption = {
			type: 'boolean',
			default: false,
		};
	}

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
	}

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
	newProps.id = attributes.anchor || uuid();

	if ( attributes.ampAnimationType ) {
		ampAttributes[ 'animate-in' ] = attributes.ampAnimationType;

		if ( attributes.ampAnimationDelay ) {
			ampAttributes[ 'animate-in-delay' ] = parseInt( attributes.ampAnimationDelay ) + 'ms';
		}

		if ( attributes.ampAnimationDuration ) {
			ampAttributes[ 'animate-in-duration' ] = parseInt( attributes.ampAnimationDuration ) + 'ms';
		}

		if ( attributes.ampAnimationAfter ) {
			ampAttributes[ 'animate-in-after' ] = attributes.ampAnimationAfter;
		}
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
 * @param {Object} element    Block element.
 * @param {Object} blockType  Block type object.
 * @param {Object} attributes Block type name.
 *
 * @return {Object} The wrapped element.
 */
export const wrapBlocksInGridLayer = ( element, blockType, attributes ) => {
	if ( ! element || ! ALLOWED_MOVABLE_BLOCKS.includes( blockType.name ) ) {
		return element;
	}

	const style = {
		style: {},
	};
	if ( 'undefined' !== typeof attributes.positionTop && 'undefined' !== typeof attributes.positionLeft ) {
		const positionStyle = {
			position: 'absolute',
			top: `${ attributes.positionTop }%`,
			left: `${ attributes.positionLeft }%`,
		};
		style.style = {
			...style.style,
			...positionStyle,
		};
	}

	if ( attributes.rotationAngle ) {
		const rotationAngle = parseInt( attributes.rotationAngle );
		const rotationStyle = {
			transform: `rotate(${ rotationAngle }deg)`,
		};
		style.style = {
			...style.style,
			...rotationStyle,
		};
	}

	// If the block has width and height set, set responsive values. Exclude text blocks since these already have it handled.
	if ( attributes.width && attributes.height ) {
		const resizeStyle = {
			width: `${ getPercentageFromPixels( 'x', attributes.width ) }%`,
			height: `${ getPercentageFromPixels( 'y', attributes.height ) }%`,
		};
		style.style = {
			...style.style,
			...resizeStyle,
		};
	}

	return (
		<amp-story-grid-layer template="vertical">
			<div className="amp-story-block-wrapper" { ...style }>
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
 * @return {number} Calculated font size.
 */
export const calculateFontSize = ( measurer, expectedHeight, expectedWidth, maxFontSize, minFontSize ) => {
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
	return minFontSize;
};

/**
 * Get percentage of a distance compared to the full width / height of the page.
 *
 * @param {string} axis X or Y axis.
 * @param {number} pixelValue Value in pixels.
 * @return {number} Value in percentage.
 */
export const getPercentageFromPixels = ( axis, pixelValue ) => {
	if ( 'x' === axis ) {
		return Math.round( ( pixelValue / STORY_PAGE_INNER_WIDTH ) * 100 );
	} else if ( 'y' === axis ) {
		return Math.round( ( pixelValue / STORY_PAGE_INNER_HEIGHT ) * 100 );
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
 * Object of block attributes to set to default when inserting a template.
 */
const emptyTemplateMapping = {
	// @todo This can use just arrays of attribute keys instead of object.
	'amp/amp-story-text': {
		content: '',
	},
	'amp/amp-story-page': {
		mediaUrl: null,
		mediaType: null,
		focalPoint: {},
	},
	'core/image': {
		url: null,
		positionLeft: null,
	},
	'amp/amp-story-cta': {
		text: null,
		link: null,
	},
	'core/quote': {
		citation: null,
		value: null,
	},
};

/**
 * Gets a skeleton template block from pre-populated block.
 *
 * @param {Object}   block            Original block.
 * @param {Object}   block.name       Block name.
 * @param {Object[]} block.attributes Block attributes.
 *
 * @return {Object} Block object.
 */
const getSkeletonTemplateBlock = ( block ) => {
	if ( ! emptyTemplateMapping[ block.name ] ) {
		return block.attributes;
	}

	const attributes = {};
	each( block.attributes, function( value, key ) {
		if ( undefined === emptyTemplateMapping[ block.name ][ key ] ) {
			attributes[ key ] = value;
		}
	} );

	// Image block's left positioning should be set to 0.
	if ( 'core/image' === block.name ) {
		attributes.positionLeft = 0;
	}

	return attributes;
};

/**
 * Creates a skeleton template from pre-populated template.
 *
 * @param {Object}   template             Block object.
 * @param {Object}   template.name        Block name.
 * @param {Object[]} template.innerBlocks List of inner blocks.
 * @param {Object[]} template.attributes  Block attributes.
 *
 * @return {Object} Skeleton template block.
 */
export const createSkeletonTemplate = ( template ) => {
	const children = [];
	template.innerBlocks.forEach( function( childBlock ) {
		children.push( createBlock( childBlock.name, getSkeletonTemplateBlock( childBlock ) ) );
	} );
	return createBlock( template.name, getSkeletonTemplateBlock( template ), children );
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
 * @param {number}  autoFontSize          The automatically determined font sized. Used when ampFitText is true.
 * @param {?string} backgroundColor       A string containing the background color slug.
 * @param {?string} textColor             A string containing the color slug.
 * @param {string}  customBackgroundColor A string containing the custom background color value.
 * @param {string}  customTextColor       A string containing the custom color value.
 * @param {number}  width                 The block's width in pixels.
 * @param {number}  height                The block's height in pixels.
 * @param {?number} opacity               Opacity.
 *
 * @return {Object} Block inline style.
 */
export const getStylesFromBlockAttributes = ( {
	align,
	fontSize,
	customFontSize,
	ampFitText,
	autoFontSize,
	backgroundColor,
	textColor,
	customBackgroundColor,
	customTextColor,
	opacity,
} ) => {
	const textClass = getColorClassName( 'color', textColor );

	const { colors } = select( 'core/block-editor' ).getSettings();

	/*
     * Calculate font size using vw to make it responsive.
     *
     * Get the font size in px based on the slug with fallback to customFontSize.
     */
	const userFontSize = fontSize ? getFontSizeFromSlug( fontSize ) : customFontSize;
	const fontSizeResponsive = userFontSize && ( ( userFontSize / STORY_PAGE_INNER_WIDTH ) * 100 ).toFixed( 2 ) + 'vw';

	const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

	return {
		backgroundColor: appliedBackgroundColor,
		color: textClass ? undefined : customTextColor,
		fontSize: ampFitText ? autoFontSize : fontSizeResponsive,
		textAlign: align,
	};
};

/**
 * Get font size from slug.
 *
 * @param {string} slug A string containing the font slug.
 *
 * @return {number} Font size in pixels.
 */
const getFontSizeFromSlug = ( slug ) => {
	switch ( slug ) {
		case 'small':
			return 19.5;
		case 'large':
			return 36.5;
		case 'huge':
			return 49.5;
		default:
			return 16;
	}
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
		save: ( { attributes } ) => {
			const className = getClassNameFromBlockAttributes( attributes );
			const styles = getStylesFromBlockAttributes( attributes );

			return (
				<RichText.Content
					tagName={ tagName }
					style={ styles }
					className={ className }
					value="{content}" // Placeholder to be replaced server-side.
				/>
			);
		},
		edit: withMetaBlockEdit( { attribute, placeholder, tagName, isEditable } ),
	};
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
