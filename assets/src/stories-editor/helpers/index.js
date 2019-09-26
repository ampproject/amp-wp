/**
 * External dependencies
 */
import uuid from 'uuid/v4';
import classnames from 'classnames';
import { every, has, isEqual } from 'lodash';
import memize from 'memize';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import '@wordpress/core-data';
import { render, cloneElement } from '@wordpress/element';
import { count } from '@wordpress/wordcount';
import { __, _x, sprintf } from '@wordpress/i18n';
import { select, dispatch } from '@wordpress/data';
import { createBlock, getBlockAttributes } from '@wordpress/blocks';
import { getColorClassName, getColorObjectByAttributeValues, getFontSize } from '@wordpress/block-editor';
import { isBlobURL } from '@wordpress/blob';

/**
 * Internal dependencies
 */
import '../store';
import {
	BlockNavigation,
	EditorCarousel,
	StoryControls,
	Shortcuts,
	MediaInserter,
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
	MAX_IMAGE_SIZE_SLUG,
	VIDEO_BACKGROUND_TYPE,
	IMAGE_BACKGROUND_TYPE,
	ANIMATION_DURATION_DEFAULTS,
} from '../constants';
import {
	MAX_FONT_SIZE,
	MIN_FONT_SIZE,
} from '../../common/constants';
import { getMinimumFeaturedImageDimensions, getBackgroundColorWithOpacity } from '../../common/helpers';
import { coreDeprecations } from '../deprecations/core-blocks';
import {
	addAMPExtraPropsDeprecations,
	wrapBlockInGridLayerDeprecations,
	addAMPAttributesDeprecations,
} from '../deprecations/filters';
import { default as MetaBlockDeprecated } from '../deprecations/story-meta-block';

const { ampStoriesFonts } = window;

const {
	getBlocksByClientId,
	getBlockRootClientId,
	getBlockOrder,
	getBlock,
	getClientIdsWithDescendants,
	getSettings,
	canInsertBlockType,
	getBlockListSettings,
} = select( 'core/block-editor' );

const { getAnimatedBlocks } = select( 'amp/story' );

const {
	addAnimation,
	changeAnimationType,
	changeAnimationDuration,
	changeAnimationDelay,
} = dispatch( 'amp/story' );

const { saveMedia } = dispatch( 'core' );
const { updateBlockAttributes } = dispatch( 'core/block-editor' );

export const isMovableBlock = ( name ) => ALLOWED_MOVABLE_BLOCKS.includes( name );

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
export const getDefaultMinimumBlockHeight = ( name ) => {
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

	if ( settings.attributes.deprecated && addAMPAttributesDeprecations[ settings.attributes.deprecated.default ] ) {
		const deprecateAMPAttributes = addAMPAttributesDeprecations[ settings.attributes.deprecated.default ];
		if ( 'function' === typeof deprecateAMPAttributes ) {
			return deprecateAMPAttributes( settings, name );
		}
	}

	const isImageBlock = 'core/image' === name;
	const isVideoBlock = 'core/video' === name;
	const isCTABlock = 'amp/amp-story-cta' === name;

	const needsTextSettings = BLOCKS_WITH_TEXT_SETTINGS.includes( name );

	// Image block already has width and height.
	const needsWidthHeight = BLOCKS_WITH_RESIZING.includes( name ) && ! isImageBlock;

	const addedAttributes = {
		addedAttributes: {
			type: 'number',
			default: 0,
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

	if ( isCTABlock ) {
		addedAttributes.anchor = {
			type: 'string',
			source: 'attribute',
			attribute: 'id',
			selector: 'amp-story-cta-layer',
		};
	}

	if ( isMovableBlock( name ) ) {
		addedAttributes.anchor = {
			type: 'string',
		};

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
			type: 'string',
		};
		addedAttributes.ampAnimationDelay = {
			default: 0,
		};
		addedAttributes.ampAnimationDuration = {
			default: 0,
		};
		addedAttributes.ampAnimationAfter = {
			type: 'string',
		};
	}

	if ( isImageBlock ) {
		addedAttributes.ampShowImageCaption = {
			type: 'boolean',
			default: false,
		};
	}

	if ( isVideoBlock ) {
		addedAttributes.ampShowCaption = {
			type: 'boolean',
			default: false,
		};

		addedAttributes.ampAriaLabel = {
			type: 'string',
			default: '',
		};

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

export const deprecateCoreBlocks = ( settings, name ) => {
	if ( ! isMovableBlock( name ) ) {
		return settings;
	}

	let deprecated = settings.deprecated ? settings.deprecated : [];
	const blockDeprecation = coreDeprecations[ name ] || undefined;
	if ( blockDeprecation ) {
		deprecated = [ ...deprecated, ...blockDeprecation ];
		return {
			...settings,
			deprecated,
		};
	}

	return settings;
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
	if ( ! isMovableBlock( name ) ) {
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

	if ( attributes.deprecated && addAMPExtraPropsDeprecations[ attributes.deprecated ] ) {
		const deprecatedExtraProps = addAMPExtraPropsDeprecations[ attributes.deprecated ];
		if ( 'function' === typeof deprecatedExtraProps ) {
			return deprecatedExtraProps( props, blockType, attributes );
		}
	}

	if ( attributes.rotationAngle ) {
		let style = ! props.style ? {} : props.style;
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
		...props,
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
 * @param {ReactElement} element                  Block element.
 * @param {Object}       blockType                Block type object.
 * @param {Object}       attributes               Block attributes.
 * @param {number}       attributes.positionTop   Top offset in pixel.
 * @param {number}       attributes.positionLeft  Left offset in pixel.
 * @param {number}       attributes.rotationAngle Rotation angle in degrees.
 * @param {number}       attributes.width         Block width in pixels.
 * @param {number}       attributes.height        Block height in pixels.
 *
 * @return {ReactElement} The wrapped element.
 */
export const wrapBlocksInGridLayer = ( element, blockType, attributes ) => {
	if ( ! element || ! isMovableBlock( blockType.name ) ) {
		return element;
	}

	if ( attributes.deprecated && wrapBlockInGridLayerDeprecations[ attributes.deprecated ] ) {
		const deprecateWrapBlocksInGridLayer = wrapBlockInGridLayerDeprecations[ attributes.deprecated ];
		if ( 'function' === typeof deprecateWrapBlocksInGridLayer ) {
			return deprecateWrapBlocksInGridLayer( element, blockType, attributes );
		}
	}
	return element;
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

	if ( editorBlockList && ! document.getElementById( 'amp-story-editor' ) ) {
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

		if ( ! document.getElementById( 'amp-story-media-inserter' ) ) {
			const mediaInserter = document.createElement( 'div' );
			mediaInserter.id = 'amp-story-media-inserter';

			editorBlockNavigation.parentNode.parentNode.insertBefore( mediaInserter, editorBlockNavigation.parentNode.nextSibling );

			render(
				<MediaInserter />,
				mediaInserter
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

	// Prevent WritingFlow component from focusing on last text field when clicking below the carousel.
	const writingFlowClickRedirectElement = document.querySelector( '.block-editor-writing-flow__click-redirect' );
	if ( writingFlowClickRedirectElement ) {
		writingFlowClickRedirectElement.remove();
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
	measurer.classList.toggle( 'is-measuring' );

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

	measurer.classList.toggle( 'is-measuring' );

	return minFontSize;
};

/**
 * Get percentage of a distance compared to the full width / height of the page.
 *
 * @param {string} axis       X or Y axis.
 * @param {number} pixelValue Value in pixels.
 * @param {number} baseValue  Value to compare against to get percentage from.
 *
 * @return {number} Value in percentage.
 */
export const getPercentageFromPixels = ( axis, pixelValue, baseValue = 0 ) => {
	if ( ! baseValue ) {
		if ( 'x' === axis ) {
			baseValue = STORY_PAGE_INNER_WIDTH;
		} else if ( 'y' === axis ) {
			baseValue = STORY_PAGE_INNER_HEIGHT;
		} else {
			return 0;
		}
	}
	return Number( ( ( pixelValue / baseValue ) * 100 ).toFixed( 2 ) );
};

/**
 * Get pixel value from percentage, based on a base value to measure against.
 * By default the full width / height of the page.
 *
 * @param {string} axis            X or Y axis.
 * @param {number} percentageValue Value in percent.
 * @param {number} baseValue       Value to compare against to get pixels from.
 *
 * @return {number} Value in percentage.
 */
export const getPixelsFromPercentage = ( axis, percentageValue, baseValue = 0 ) => {
	if ( ! baseValue ) {
		if ( 'x' === axis ) {
			baseValue = STORY_PAGE_INNER_WIDTH;
		} else if ( 'y' === axis ) {
			baseValue = STORY_PAGE_INNER_HEIGHT;
		}
	}
	return Math.round( ( percentageValue / 100 ) * baseValue );
};

/**
 * Returns the minimum dimensions for a story poster image.
 *
 * @see https://www.ampproject.org/docs/reference/components/amp-story#poster-guidelines-(for-poster-portrait-src,-poster-landscape-src,-and-poster-square-src)
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
	const attributesToKeep = [ 'positionTop', 'positionLeft', 'btnPositionTop', 'btnPositionLeft', 'width', 'height', 'tagName', 'align', 'content', 'text', 'value', 'citation', 'autoFontSize', 'rotationAngle' ];

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
 * @param {Object}   attributes                       Block attributes.
 * @param {string[]} attributes.className             List of pre-existing class names for the block.
 * @param {boolean}  attributes.ampFitText            Whether amp-fit-text should be used or not.
 * @param {?string}  attributes.backgroundColor       A string containing the background color slug.
 * @param {?string}  attributes.textColor             A string containing the color slug.
 * @param {string}   attributes.customBackgroundColor A string containing the custom background color value.
 * @param {string}   attributes.customTextColor       A string containing the custom color value.
 * @param {?number}  attributes.opacity               Opacity.
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
 * @param {Object}  attributes                       Block attributes.
 * @param {string}  attributes.align                 Block alignment.
 * @param {?string} attributes.fontSize              Font size slug.
 * @param {?number} attributes.customFontSize        Custom font size in pixels.
 * @param {boolean} attributes.ampFitText            Whether amp-fit-text should be used or not.
 * @param {?string} attributes.backgroundColor       A string containing the background color slug.
 * @param {?string} attributes.textColor             A string containing the color slug.
 * @param {string}  attributes.customBackgroundColor A string containing the custom background color value.
 * @param {string}  attributes.customTextColor       A string containing the custom color value.
 * @param {?number} attributes.opacity               Opacity.
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
		textAlign: align ? align : undefined,
	};
};

/**
 * Returns the settings object for the AMP story meta blocks (post title, author, date).
 *
 * @param {Object}  args               Function arguments.
 * @param {string}  args.attribute     The post attribute this meta block reads from.
 * @param {string}  args.placeholder   Optional. Placeholder text in case the attribute is empty.
 * @param {string}  [args.tagName]     Optional. The HTML tag name to use for the content. Default '<p>'.
 * @param {boolean} [args.isEditable]  Optional. Whether the meta block is editable by the user or not. Default false.
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
		deprecated: MetaBlockDeprecated( { tagName } ),
	};
};

/**
 * Removes a pre-set caption from image and video block.
 *
 * @param {string} clientId Block ID.
 */
export const maybeRemoveMediaCaption = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block ) {
		return;
	}

	const isImage = 'core/image' === block.name;
	const isVideo = 'core/video' === block.name;

	if ( ! isImage && ! isVideo ) {
		return;
	}

	const { attributes } = block;

	// If we have an image or video with pre-set caption we should remove the caption.
	if (
		( ( ! attributes.ampShowImageCaption && isImage ) || ( ! attributes.ampShowCaption && isVideo ) ) &&
			attributes.caption &&
			0 !== attributes.caption.length
	) {
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
			return document.querySelector( `#block-${ clientId } .block-editor-rich-text__editable` );

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const slug = name.replace( '/', '-' );
			return document.querySelector( `#block-${ clientId } .wp-block-${ slug }` );

		default:
			return null;
	}
};

/**
 * Returns a movable block's inner element.
 *
 * @param {Object} block Block object.
 *
 * @return {?Element} The inner element.
 */
export const getBlockInnerElement = ( block ) => {
	const { name, clientId } = block;
	const isPage = 'amp/amp-story-page' === name;
	const isCTABlock = 'amp/amp-story-cta' === name;

	if ( isPage ) {
		return document.querySelector( `[data-block="${ clientId }"]` );
	}

	if ( isCTABlock ) {
		// Not the block itself is movable, only the button within.
		return document.querySelector( `amp-story-cta-button-${ clientId }` );
	}

	return document.querySelector( `#block-${ clientId }` );
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
				const fitFontSize = calculateFontSize( element, height, width, MAX_FONT_SIZE, MIN_FONT_SIZE );

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

		default:
			break;
	}
};

/**
 * Updates a block's width and height in case it doesn't use amp-fit-text and the font size has changed.
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
export const maybeUpdateBlockDimensions = ( block ) => {
	const { name, clientId, attributes } = block;
	const { width, height, ampFitText, content } = attributes;

	if ( ampFitText ) {
		return;
	}

	switch ( name ) {
		case 'amp/amp-story-text':
			const element = getBlockInnerTextElement( block );

			if ( element && content.length ) {
				if ( element.offsetHeight > height ) {
					updateBlockAttributes( clientId, { height: element.offsetHeight } );
				}

				if ( element.offsetWidth > width ) {
					updateBlockAttributes( clientId, { width: element.offsetWidth } );
				}
			}

			break;

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const metaBlockElement = getBlockInnerTextElement( block );

			if ( metaBlockElement ) {
				metaBlockElement.classList.toggle( 'is-measuring' );

				if ( metaBlockElement.offsetHeight > height ) {
					updateBlockAttributes( clientId, { height: metaBlockElement.offsetHeight } );
				}

				if ( metaBlockElement.offsetWidth > width ) {
					updateBlockAttributes( clientId, { width: metaBlockElement.offsetWidth } );
				}

				metaBlockElement.classList.toggle( 'is-measuring' );
			}

			break;

		default:
			break;
	}
};

/**
 * Remove deprecated attribute if the block was just migrated.
 *
 * @param {Object} block Block.
 */
export const maybeRemoveDeprecatedSetting = ( block ) => {
	if ( ! block ) {
		return;
	}

	const { attributes } = block;

	// If the block was just migrated, update the block to initiate unsaved state.
	if ( attributes.deprecated && 'migrated' === attributes.deprecated ) {
		updateBlockAttributes( block.clientId, {
			deprecated: null,
		} );
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
 * Initialize animation making sure that the predecessor animation has been initialized at first.
 *
 * @param {Object} block Animated block.
 * @param {Object} page Parent page.
 * @param {Object} allBlocks All blocks.
 */
const initializeAnimation = ( block, page, allBlocks ) => {
	const { ampAnimationAfter } = block.attributes;
	let predecessor;
	if ( ampAnimationAfter ) {
		predecessor = allBlocks.find( ( b ) => b.attributes.anchor === ampAnimationAfter );
	}

	if ( predecessor ) {
		const animations = getAnimatedBlocks();
		const pageAnimationOrder = animations[ page ] || [];
		const predecessorEntry = pageAnimationOrder.find( ( { id } ) => id === predecessor.clientId );

		// We need to initialize the predecessor first.
		if ( ! predecessorEntry ) {
			initializeAnimation( predecessor, page, allBlocks );
		}
	}
	addAnimation( page, block.clientId, predecessor ? predecessor.clientId : undefined );
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
				const { ampAnimationType, ampAnimationDuration, ampAnimationDelay } = block.attributes;
				initializeAnimation( block, page, allBlocks );

				changeAnimationType( page, block.clientId, ampAnimationType );
				changeAnimationDuration( page, block.clientId, ampAnimationDuration ? parseInt( String( ampAnimationDuration ).replace( 'ms', '' ) ) : undefined );
				changeAnimationDelay( page, block.clientId, ampAnimationDelay ? parseInt( String( ampAnimationDelay ).replace( 'ms', '' ) ) : undefined );
			}
		}
	}
};

/**
 * Return a label for the block order controls depending on block position.
 *
 * @param {string}  type            Block type - in the case of a single block, should define its 'type'. I.e. 'Text', 'Heading', 'Image' etc.
 * @param {number}  currentPosition The block's current position.
 * @param {number}  newPosition     The block's new position.
 * @param {boolean} isFirst         This is the first block.
 * @param {boolean} isLast          This is the last block.
 * @param {number}  dir             Direction of movement (> 0 is considered to be going down, < 0 is up).
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

	return undefined;
};

/**
 * Get block by Page ID and block name.
 *
 * @param {string} pageClientId Root ID.
 * @param {string} blockName Block name.
 * @return {Object} Found block.
 */
const getPageBlockByName = ( pageClientId, blockName ) => {
	const innerBlocks = getBlocksByClientId( getBlockOrder( pageClientId ) );
	return innerBlocks.find( ( { name } ) => name === blockName );
};

/**
 * Get CTA block.
 *
 * @param {Array} pageClientId Root ID.
 * @return {Object} CTA block.
 */
export const getCallToActionBlock = ( pageClientId ) => {
	return getPageBlockByName( pageClientId, 'amp/amp-story-cta' );
};

/**
 * Get Page Attachment block.
 *
 * @param {Array} pageClientId Root ID.
 * @return {Object} Page Attachment block.
 */
export const getPageAttachmentBlock = ( pageClientId ) => {
	return getPageBlockByName( pageClientId, 'amp/amp-story-page-attachment' );
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

/**
 * Returns an image of the first frame of a given video.
 *
 * @param {string} src Video src URL.
 * @return {Promise<string>} The extracted image in base64-encoded format.
 */
export const getFirstFrameOfVideo = ( src ) => {
	const video = document.createElement( 'video' );
	video.muted = true;
	video.crossOrigin = 'anonymous';
	video.preload = 'metadata';
	video.currentTime = 0.5; // Needed to seek forward.

	return new Promise( ( resolve, reject ) => {
		video.addEventListener( 'error', reject );

		video.addEventListener( 'canplay', () => {
			const canvas = document.createElement( 'canvas' );
			canvas.width = video.videoWidth;
			canvas.height = video.videoHeight;

			const ctx = canvas.getContext( '2d' );
			ctx.drawImage( video, 0, 0, canvas.width, canvas.height );

			canvas.toBlob( resolve, 'image/jpeg' );
		} );

		video.src = src;
	} );
};

/**
 * Uploads the video's first frame as an attachment.
 *
 * @param {Object} media Media object.
 * @param {number} media.id  Video ID.
 * @param {string} media.src Video URL.
 */
export const uploadVideoFrame = async ( { id: videoId, src } ) => {
	const { __experimentalMediaUpload: mediaUpload } = getSettings();

	const img = await getFirstFrameOfVideo( src );

	return new Promise( ( resolve, reject ) => {
		mediaUpload( {
			filesList: [ img ],
			onFileChange: ( [ fileObj ] ) => {
				const { id: posterId, url: posterUrl } = fileObj;

				if ( videoId && posterId ) {
					saveMedia( {
						id: videoId,
						featured_media: posterId,
					} );

					saveMedia( {
						id: posterId,
						meta: {
							amp_is_poster: true,
						},
					} );
				}

				if ( ! isBlobURL( posterUrl ) ) {
					resolve( fileObj );
				}
			},
			onError: reject,
		} );
	} );
};

/**
 * Given a media object, returns a suitable poster image URL.
 *
 * @param {Object} fileObj Media object.
 * @return {string} Poster image URL.
 */
export const getPosterImageFromFileObj = ( fileObj ) => {
	const { url } = fileObj;

	let newPoster = url;

	if ( has( fileObj, [ 'media_details', 'sizes', MAX_IMAGE_SIZE_SLUG, 'source_url' ] ) ) {
		newPoster = fileObj.media_details.sizes[ MAX_IMAGE_SIZE_SLUG ].source_url;
	} else if ( has( fileObj, [ 'media_details', 'sizes', 'large', 'source_url' ] ) ) {
		newPoster = fileObj.media_details.sizes.large.source_url;
	}

	return newPoster;
};

/**
 * Add anchor for a block if it's missing.
 *
 * @param {string} clientId Block ID.
 */
export const maybeAddMissingAnchor = ( clientId ) => {
	const block = getBlock( clientId );
	if ( ! block ) {
		return;
	}
	if ( ! block.attributes.anchor ) {
		updateBlockAttributes( block.clientId, { anchor: getUniqueId() } );
	}
};

/**
 * Given a rotation angle, finds the closest angle to snap to.
 *
 * Inspired by the implementation in re-resizable.
 *
 * @see https://github.com/bokuweb/re-resizable
 *
 * @param {number} number
 * @param {Array|Function<number>} snap List of snap targets or function that provider
 * @param {number} snapGap Minimum gap required in order to move to the next snapping target
 * @return {number} New angle.
 */
export const findClosestSnap = memize( ( number, snap, snapGap ) => {
	const snapArray = typeof snap === 'function' ? snap( number ) : snap;

	const closestGapIndex = snapArray.reduce(
		( prev, curr, index ) => ( Math.abs( curr - number ) < Math.abs( snapArray[ prev ] - number ) ? index : prev ),
		0,
	);
	const gap = Math.abs( snapArray[ closestGapIndex ] - number );

	return snapGap === 0 || gap < snapGap ? snapArray[ closestGapIndex ] : number;
} );

/**
 * Sets input selection to the end for being able to type to the end of the existing text.
 *
 * @param {string} inputSelector Text input selector.
 */
export const setInputSelectionToEnd = ( inputSelector ) => {
	const textInput = document.querySelector( inputSelector );
	// Create selection, collapse it in the end of the content.
	if ( textInput ) {
		const range = document.createRange();
		range.selectNodeContents( textInput );
		range.collapse( false );
		const selection = window.getSelection();
		selection.removeAllRanges();
		selection.addRange( range );
	}
};

/**
 * Helper to process media object and return attributes to be saved.
 *
 * @param {Object} media Attachment object to be processed.
 *
 * @return {Object} Processed Object to save to block attributes.
 */
export const processMedia = ( media ) => {
	if ( ! media || ! media.url ) {
		return {
			mediaUrl: undefined,
			mediaId: undefined,
			mediaType: undefined,
			mediaAlt: undefined,
			poster: undefined,
		};
	}

	let mediaType;

	// For media selections originated from a file upload.
	if ( media.media_type ) {
		if ( media.media_type === VIDEO_BACKGROUND_TYPE ) {
			mediaType = VIDEO_BACKGROUND_TYPE;
		} else {
			mediaType = IMAGE_BACKGROUND_TYPE;
		}
	} else {
		// For media selections originated from existing files in the media library.
		if (
			media.type !== IMAGE_BACKGROUND_TYPE &&
			media.type !== VIDEO_BACKGROUND_TYPE
		) {
			return {
				mediaUrl: undefined,
				mediaId: undefined,
				mediaType: undefined,
				mediaAlt: undefined,
				poster: undefined,
			};
		}

		mediaType = media.type;
	}

	const mediaAlt = media.alt || media.title;
	const mediaUrl = media.url;
	const poster = VIDEO_BACKGROUND_TYPE === mediaType && media.image && media.image.src !== media.icon ? media.image.src : undefined;

	return {
		mediaUrl,
		mediaId: media.id,
		mediaType,
		mediaAlt,
		poster,
	};
};

/**
 * Helper to convert snake_case meta keys to key names used in the amp-story-page attributes.
 *
 * @param {Object} meta Meta object to be converted to an object with attributes key names.
 *
 * @return {Object} Processed object.
 */
export const metaToAttributeNames = ( meta ) => {
	return {
		autoAdvanceAfter: meta.amp_story_auto_advance_after,
		autoAdvanceAfterDuration: meta.amp_story_auto_advance_after_duration,
	};
};

/**
 * Helper to add an `aria-label` to video elements when saved.
 *
 * This helper is designed as a filter for `blocks.getSaveElement`.
 *
 * @param {ReactElement}  element     Previously generated React element
 * @param {Object}        type        Block type definition.
 * @param {string}        type.name   Name of block type
 * @param {Object}        attributes  Block attributes.
 *
 * @return {ReactElement}  New React element
 */
export const addVideoAriaLabel = ( element, { name }, attributes ) => {
	// this filter only applies to core video objects (which always has children) where an aria label has been set
	if ( name !== 'core/video' || ! element.props.children || ! attributes.ampAriaLabel ) {
		return element;
	}

	/* `element` will be a react structure like:

	<figure>
		<amp-video|video>
			Fallback content
		</amp-video|video>
		[<figcaption>Caption</figcaption>]
	</figure>

	The video element can be either an `<amp-video>`` or a regular `<video>`.

	`<figcaption>` is not necessarily present.

	We need to hook into this element and add an `aria-label` on the `<amp-video|video>` element.
	*/

	const isFigure = element.type === 'figure';
	const childNodes = Array.isArray( element.props.children ) ? element.props.children : [ element.props.children ];
	const videoTypes = [ 'amp-video', 'video' ];
	const isFirstChildVideoType = videoTypes.includes( childNodes[ 0 ].type );
	if ( ! isFigure || ! isFirstChildVideoType ) {
		return element;
	}

	const figure = element;
	const [ video, ...rest ] = childNodes;

	const newVideo = cloneElement(
		video,
		{ 'aria-label': attributes.ampAriaLabel },
		video.props.children,
	);

	const newFigure = cloneElement(
		figure,
		{},
		newVideo,
		...rest
	);

	return newFigure;
};

/**
 * Copy text to clipboard by using temporary input field.
 *
 * @param {string} text Text to copy.
 */
export const copyTextToClipBoard = ( text ) => {
	// Create temporary input element for being able to copy.
	const tmpInput = document.createElement( 'textarea' );
	tmpInput.setAttribute( 'readonly', '' );
	tmpInput.style = {
		position: 'absolute',
		left: '-9999px',
	};
	tmpInput.value = text;
	document.body.appendChild( tmpInput );
	tmpInput.select();
	document.execCommand( 'copy' );
	// Remove the temporary element.
	document.body.removeChild( tmpInput );
};

/**
 * Ensure that only allowed blocks are pasted.
 *
 * @param {[]}      blocks Array of blocks.
 * @param {string}  pageId Page ID.
 * @return {[]} Filtered blocks.
 */
export const ensureAllowedBlocksOnPaste = ( blocks, pageId ) =>
	blocks.filter( ( block ) => isBlockAllowedOnPage( block.name, pageId ) );

/**
 * Is the given block allowed on the given page?
 *
 * @param {Object}  name The name of the block to test.
 * @param {string}  pageId Page ID.
 * @return {boolean} Returns true if the element is allowed on the page, false otherwise.
 */
export const isBlockAllowedOnPage = ( name, pageId ) => {
	// canInsertBlockType() alone is not enough, see https://github.com/WordPress/gutenberg/issues/14515
	const blockSettings = getBlockListSettings( pageId );
	return canInsertBlockType( name, pageId ) && blockSettings && blockSettings.allowedBlocks.includes( name );
};

/**
 * Given a block client ID, returns the corresponding DOM node for the block,
 * if exists. As much as possible, this helper should be avoided, and used only
 * in cases where isolated behaviors need remote access to a block node.
 *
 * @param {string} clientId Block client ID.
 * @param {Element} scope an optional DOM Element to which the selector should be scoped
 *
 * @return {Element} Block DOM node.
 */
export const getBlockDOMNode = ( clientId, scope = document ) => {
	return scope.querySelector( `[data-block="${ clientId }"]` );
};

/**
 * Returns a movable block's wrapper element.
 *
 * @param {Object} block Block object.
 *
 * @return {null|Element} The inner element.
 */
export const getBlockWrapperElement = ( block ) => {
	if ( ! block ) {
		return null;
	}

	const { name, clientId } = block;

	if ( ! isMovableBlock( name ) ) {
		return null;
	}

	return document.querySelector( `.amp-page-child-block[data-block="${ clientId }"]` );
};

/**
 * Calculate target scaling factor so that it is at least 25% larger than the
 * page.
 *
 * A copy of the same method in the AMP framework.
 *
 * @see https://github.com/ampproject/amphtml/blob/13b3b6d92ee0565c54ec34732e88f01847aa8a91/extensions/amp-story/1.0/animation-presets-utils.js#L91-L111
 *
 * @param {number} width Target width.
 * @param {number} height Target height.
 *
 * @return {number} Target scaling factor.
 */
export const calculateTargetScalingFactor = ( width, height ) => {
	const targetFitsWithinPage = width <= STORY_PAGE_INNER_WIDTH || height <= STORY_PAGE_INNER_HEIGHT;

	if ( targetFitsWithinPage ) {
		const scalingFactor = 1.25;

		const widthFactor = STORY_PAGE_INNER_WIDTH > width ? STORY_PAGE_INNER_WIDTH / width : 1;
		const heightFactor = STORY_PAGE_INNER_HEIGHT > height ? STORY_PAGE_INNER_HEIGHT / height : 1;

		return Math.max( widthFactor, heightFactor ) * scalingFactor;
	}

	return 1;
};

/**
 * Returns the block's actual position in relation to the page it's on.
 *
 * @param {Element} blockElement Block element.
 * @param {Element} parentElement The block parent element.
 *
 * @return {{top: number, left: number}} Relative position of the block.
 */
export const getRelativeElementPosition = ( blockElement, parentElement ) => {
	const { left: parentLeft, top: parentTop } = parentElement.getBoundingClientRect();
	const { top, left } = blockElement.getBoundingClientRect();

	return {
		top: top - parentTop,
		left: left - parentLeft,
	};
};

/**
 * Calculates the offsets and scaling factors for animation playback.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 * @return {{offsetX: number, offsetY: number, scalingFactor: number}} Animation transform parameters.
 */
const getAnimationTransformParams = ( block, animationType ) => {
	const blockElement = getBlockWrapperElement( block );
	const innerElement = getBlockInnerElement( block );
	const parentBlock = getBlockRootClientId( block.clientId );
	const parentBlockElement = document.querySelector( `[data-block="${ parentBlock }"]` );

	const width = innerElement.offsetWidth;
	const height = innerElement.offsetHeight;
	const { top, left } = getRelativeElementPosition( blockElement, parentBlockElement );

	let offsetX;
	let offsetY;
	let scalingFactor;

	switch ( animationType ) {
		case 'fly-in-left':
		case 'rotate-in-left':
		case 'whoosh-in-left':
			offsetX = -( left + width );
			break;
		case 'fly-in-right':
		case 'rotate-in-right':
		case 'whoosh-in-right':
			offsetX = STORY_PAGE_INNER_WIDTH + left + width;
			break;
		case 'fly-in-top':
			offsetY = -( top + height );
			break;
		case 'fly-in-bottom':
			offsetY = STORY_PAGE_INNER_HEIGHT + top + height;
			break;
		case 'drop':
			offsetY = Math.max( 160, ( top + height ) );
			break;
		case 'pan-left':
		case 'pan-right':
			scalingFactor = calculateTargetScalingFactor( width, height );
			offsetX = STORY_PAGE_INNER_WIDTH - ( width * scalingFactor );
			offsetY = ( STORY_PAGE_INNER_HEIGHT - ( height * scalingFactor ) ) / 2;
			break;
		case 'pan-down':
		case 'pan-up':
			scalingFactor = calculateTargetScalingFactor( width, height );
			offsetX = -( width * scalingFactor ) / 2;
			offsetY = STORY_PAGE_INNER_HEIGHT - ( height * scalingFactor );
			break;
		default:
			offsetX = 0;
	}

	return {
		offsetX,
		offsetY,
		scalingFactor,
	};
};

/**
 * Sets the needed CSS custom properties and class name for animation playback.
 *
 * This way the initial animation state can be displayed without having to actually
 * start the animation.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 */
export const setAnimationTransformProperties = ( block, animationType ) => {
	const blockElement = getBlockWrapperElement( block );

	if ( ! blockElement || ! animationType ) {
		return;
	}

	resetAnimationProperties( block, animationType );

	const { offsetX, offsetY, scalingFactor } = getAnimationTransformParams( block, animationType );

	if ( offsetX ) {
		blockElement.style.setProperty( '--animation-offset-x', `${ offsetX }px` );
	}

	if ( offsetY ) {
		blockElement.style.setProperty( '--animation-offset-y', `${ offsetY }px` );
	}

	if ( scalingFactor ) {
		blockElement.style.setProperty( '--animation-scale-start', scalingFactor );
		blockElement.style.setProperty( '--animation-scale-end', scalingFactor );
	}

	blockElement.classList.add( `story-animation-init-${ animationType }` );
};

/**
 * Removes all inline styles and class name previously set for animation playback.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 */
export const resetAnimationProperties = ( block, animationType ) => {
	const blockElement = getBlockWrapperElement( block );

	if ( ! blockElement || ! animationType ) {
		return;
	}

	blockElement.classList.remove( `story-animation-init-${ animationType }` );
	blockElement.classList.remove( `story-animation-${ animationType }` );
	blockElement.style.removeProperty( '--animation-offset-x' );
	blockElement.style.removeProperty( '--animation-offset-y' );
	blockElement.style.removeProperty( '--animation-scale-start' );
	blockElement.style.removeProperty( '--animation-scale-end' );
	blockElement.style.removeProperty( '--animation-duration' );
	blockElement.style.removeProperty( '--animation-delay' );
};

/**
 * Plays the block's animation in the editor.
 *
 * Assumes that setAnimationTransformProperties() has been called before.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 * @param {number} animationDuration Animation duration.
 * @param {number} animationDelay Animation delay.
 * @param {Function} callback Callback for when animation has stopped.
 */
export const startAnimation = ( block, animationType, animationDuration, animationDelay, callback = () => {} ) => {
	const blockElement = getBlockWrapperElement( block );

	if ( ! blockElement || ! animationType ) {
		callback();

		return;
	}

	const DEFAULT_ANIMATION_DURATION = ANIMATION_DURATION_DEFAULTS[ animationType ] || 0;

	blockElement.classList.remove( `story-animation-init-${ animationType }` );

	blockElement.style.setProperty( '--animation-duration', `${ animationDuration || DEFAULT_ANIMATION_DURATION }ms` );
	blockElement.style.setProperty( '--animation-delay', `${ animationDelay || 0 }ms` );

	blockElement.classList.add( `story-animation-${ animationType }` );

	blockElement.addEventListener( 'animationend', callback, { once: true } );
};

/**
 * Check if block is page block.
 *
 * @param {string} clientId Block client ID.
 * @return {boolean} Boolean if block is / is not a page block.
 */
export const isPageBlock = ( clientId ) => {
	const block = getBlock( clientId );
	return block && 'amp/amp-story-page' === block.name;
};
