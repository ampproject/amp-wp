/**
 * External dependencies
 */
import uuid from 'uuid/v4';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { count } from '@wordpress/wordcount';
import { __, _x } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { getColorClassName, getColorObjectByAttributeValues, getFontSize, RichText } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import {
	BlockNavigation,
	EditorCarousel,
	StoryControls,
	Shortcuts,
	withMetaBlockEdit,
	Inserter,
} from './components';
import {
	ALLOWED_CHILD_BLOCKS,
	ALLOWED_MOVABLE_BLOCKS,
	ALLOWED_TOP_LEVEL_BLOCKS,
	BLOCK_TAG_MAPPING,
	STORY_PAGE_INNER_WIDTH,
	STORY_PAGE_INNER_HEIGHT,
} from './constants';
import ampStoriesFonts from 'amp-stories-fonts';

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
 * Add AMP attributes to every allowed AMP Story block.
 *
 * @param {Object} settings Settings.
 * @param {string} name Block name.
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
			selector: 'amp-story-grid-layer > *, amp-story-cta-layer',
		},
		ampAnimationType: {
			type: 'string',
		},
		ampAnimationDelay: {
			type: 'number',
			default: 0,
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

		addedAttributes.height = {
			type: 'number',
			default: getDefaultMinimumBlockHeight( name ),
		};

		addedAttributes.width = {
			type: 'number',
			default: 250,
		};
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

	if ( attributes.rotationAngle ) {
		const rotationAngle = parseInt( attributes.rotationAngle );
		const rotationStyle = {
			transform: `rotate(${ rotationAngle }deg)`,
		};
		ampAttributes.style = {
			...ampAttributes.style,
			...rotationStyle,
		};
	}

	return {
		...newProps,
		...ampAttributes,
	};
};

const blockContentDiv = document.createElement( 'div' );

/**
 * Filter block attributes to make sure that the className is taken even though it's wrapper in grid layer.
 *
 * @param {Object} blockAttributes Block attributes.
 * @param {Object} blockType Block type.
 * @param {string} innerHTML Inner HTML from saved content.
 * @return {Object} Block attributes.
 */
export const filterBlockAttributes = ( blockAttributes, blockType, innerHTML ) => {
	if ( ! blockAttributes.className && innerHTML.includes( 'is-style-' ) && 0 === innerHTML.indexOf( '<amp-story-grid-layer' ) ) {
		blockContentDiv.innerHTML = innerHTML;

		// Lets check the first child of the amp-story-grid-layer for the className.
		if (
			blockContentDiv.children[ 0 ].children.length &&
			blockContentDiv.children[ 0 ].children[ 0 ].className.includes( 'is-style-' )
		) {
			blockAttributes.className = blockContentDiv.children[ 0 ].children[ 0 ].className;
		}
	}

	return blockAttributes;
};

/**
 * Wraps all movable blocks in a grid layer.
 *
 * @param {Object} element
 * @param {Object} blockType
 *
 * @return {Object} The element.
 */
export const wrapBlocksInGridLayer = ( element, blockType ) => {
	if ( ! element || ! ALLOWED_MOVABLE_BLOCKS.includes( blockType.name ) ) {
		return element;
	}

	return (
		<amp-story-grid-layer template="vertical">
			{ element }
		</amp-story-grid-layer>
	);
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
		blockNavigation.id = 'amp-story-block-navigation';

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
			<BlockNavigation />,
			blockNavigation
		);

		render(
			<EditorCarousel />,
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

/**
 * Calculates font size that fits to the text element based on the element's size.
 * Replicates amp-fit-text's logic in the editor.
 *
 * @see https://github.com/ampproject/amphtml/blob/e7a1b3ff97645ec0ec482192205134bd0735943c/extensions/amp-fit-text/0.1/amp-fit-text.js
 *
 * @param {Object} measurer HTML element.
 * @param {number} expectedHeight Maximum height.
 * @param {number} expectedWidth Maximum width.
 * @param {number} maxFontSize Maximum font size.
 * @param {number} minFontSize Minimum font size.
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
 * Gets the message for an invalid featured image, if it is invalid.
 *
 * @param {Function} validateImageSize A function to validate whether the size is correct.
 * @param {string} invalidSizeMessage A message to display in a Notice if the size is wrong.
 * @return {string|null} A message about the invalid featured image, or null if it's valid.
 */
export const getFeaturedImageMessage = ( validateImageSize, invalidSizeMessage ) => {
	const currentPost = select( 'core/editor' ).getCurrentPost();
	const editedFeaturedMedia = select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
	const featuredMedia = currentPost.featured_media || editedFeaturedMedia;

	if ( ! featuredMedia ) {
		return __( 'Selecting a featured image is required.', 'amp' );
	}

	const media = select( 'core' ).getMedia( featuredMedia );
	if ( ! media || ! media.media_details || ! validateImageSize( media.media_details ) ) {
		return invalidSizeMessage;
	}
};

/**
 * Gets whether the AMP story's featured image has the right minimum dimensions.
 *
 * The featured image will be used for the poster-portrait-src.
 * It should have a width of at least 696px and a height of at least 928px.
 *
 * @param {Object} media A media object with width and height values.
 * @return {boolean} Whether the media has the minimum dimensions.
 */
export const hasMinimumStoryPosterDimensions = ( media ) => {
	const minWidth = 696;
	const minHeight = 928;
	return (
		( media.width && media.height )	&&
		( media.width >= minWidth && media.height >= minHeight )
	);
};

/**
 * Adds either background color or gradient to style depending on the settings.
 *
 * @param {Object} overlayStyle Original style.
 * @param {Array} backgroundColors Array of color settings.
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
 * Converts hex to rgba.
 *
 * @param {string} hex Hex value.
 * @param {number} opacity Opacity.
 * @return {Object} Rgba value.
 */
export const getRgbaFromHex = ( hex, opacity ) => {
	if ( ! hex ) {
		return [];
	}
	hex = hex.replace( '#', '' );
	const r = parseInt( hex.substring( 0, 2 ), 16 );
	const g = parseInt( hex.substring( 2, 4 ), 16 );
	const b = parseInt( hex.substring( 4, 6 ), 16 );
	return [
		r,
		g,
		b,
		opacity / 100,
	];
};

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
	width,
	height,
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

	const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

	return {
		backgroundColor: appliedBackgroundColor,
		color: textClass ? undefined : customTextColor,
		fontSize: ampFitText ? autoFontSize : fontSizeResponsive,
		width: `${ getPercentageFromPixels( 'x', width ) }%`,
		height: `${ getPercentageFromPixels( 'y', height ) }%`,
		textAlign: align,
	};
};

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

const getBackgroundColorWithOpacity = ( colors, backgroundColor, customBackgroundColor, opacity ) => {
	const hasOpacity = opacity && opacity < 100;
	const backgroundClass = getColorClassName( 'background-color', backgroundColor );

	let appliedBackgroundColor;

	// If we need to assign opacity.
	if ( hasOpacity && ( backgroundColor || customBackgroundColor ) ) {
		const hexColor = getColorObjectByAttributeValues( colors, backgroundColor, customBackgroundColor );

		if ( hexColor ) {
			const [ r, g, b, a ] = getRgbaFromHex( hexColor.color, opacity );

			appliedBackgroundColor = `rgba( ${ r }, ${ g }, ${ b }, ${ a })`;
		}
	} else if ( ! backgroundClass ) {
		appliedBackgroundColor = customBackgroundColor;
	}

	return appliedBackgroundColor;
};
