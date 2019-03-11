/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { compose } from '@wordpress/compose';
import domReady from '@wordpress/dom-ready';
import { getDefaultBlockName, setDefaultBlockName, getBlockTypes, unregisterBlockType } from '@wordpress/blocks';
import { select, subscribe, dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { withAttributes, withParentBlock, withBlockName, withHasSelectedInnerBlock, withAmpStorySettings, withAnimationControls, BlockDropZone } from './components';
import { ALLOWED_BLOCKS, ALLOWED_CHILD_BLOCKS, BLOCK_TAG_MAPPING } from './constants';
import { maybeEnqueueFontStyle } from './helpers';
import { store } from './stores/amp-story';

const { getSelectedBlockClientId, getBlocksByClientId, getBlock, getClientIdsWithDescendants, getBlockRootClientId } = select( 'core/editor' );
const { getAnimationOrder } = select( 'amp/story' );
const { addAnimation, removePage } = dispatch( 'amp/story' );

domReady( () => {
	setDefaultBlockName( 'amp/amp-story-page' );

	// Remove all blocks that aren't whitelisted.
	const disallowedBlockTypes = getBlockTypes().filter( ( { name } ) => ! ALLOWED_BLOCKS.includes( name ) );

	for ( const blockType of disallowedBlockTypes ) {
		unregisterBlockType( blockType.name );
	}

	const allBlocks = getBlocksByClientId( getClientIdsWithDescendants() );

	for ( const block of allBlocks ) {
		const page = getBlockRootClientId( block.clientId );

		// Set initial animation order state.
		if ( page ) {
			const ampAnimationAfter = block.attributes.ampAnimationAfter;
			const predecessor = allBlocks.find( ( b ) => b.attributes.anchor === ampAnimationAfter );

			addAnimation( page, block.clientId, predecessor ? predecessor.clientId : undefined );
		}

		// Load all needed fonts.
		if ( block.attributes.ampFontFamily ) {
			maybeEnqueueFontStyle( block.attributes.ampFontFamily );
		}
	}
} );

subscribe( () => {
	const defaultBlockName = getDefaultBlockName();
	const selectedBlockClientId = getSelectedBlockClientId();

	// Switch default block depending on context
	if ( selectedBlockClientId ) {
		const selectedBlock = getBlock( selectedBlockClientId );

		if ( 'amp/amp-story-page' === selectedBlock.name && 'amp/amp-story-page' !== defaultBlockName ) {
			setDefaultBlockName( 'amp/amp-story-page' );
		} else if ( 'amp/amp-story-page' !== selectedBlock.name && 'amp/amp-story-text' !== defaultBlockName ) {
			setDefaultBlockName( 'amp/amp-story-text' );
		}
	} else if ( ! selectedBlockClientId && 'amp/amp-story-page' !== defaultBlockName ) {
		setDefaultBlockName( 'amp/amp-story-page' );
	}
} );

store.subscribe( () => {
	const animatedPages = Object.keys( getAnimationOrder() );

	// Remove stale data from store.
	for ( const page of animatedPages ) {
		if ( ! getBlock( page ) ) {
			removePage( store.getState(), page );
		}
	}
} );

/**
 * Add AMP attributes to every allowed AMP Story block.
 *
 * @param {Object} settings Settings.
 * @param {string} name Block name.
 * @return {Object} Settings.
 */
const addAMPAttributes = ( settings, name ) => {
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
			default: '0ms',
		};
		addedAttributes.ampAnimationDuration = {
			source: 'attribute',
			selector: BLOCK_TAG_MAPPING[ name ],
			attribute: 'animate-in-duration',
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
		default: 0
	};
	addedAttributes.positionLeft = {
		type: 'number',
		default: 0
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
const addAMPExtraProps = ( props, blockType, attributes ) => {
	const ampAttributes = {};

	if ( ! ALLOWED_CHILD_BLOCKS.includes( blockType.name ) ) {
		return props;
	}

	// Always add anchor ID regardless of block support. Needed for animations.
	props.id = attributes.anchor || uuid();

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

	return {
		...props,
		...ampAttributes,
	};
};

/**
 * Filter layer properties to define the parent block.
 *
 * @param {Object} props Block properties.
 * @return {Object} Properties.
 */
const setBlockParent = ( props ) => {
	const { name } = props;

	if ( ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
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

const wrapperWithSelect = compose(
	withAttributes,
	withBlockName,
	withHasSelectedInnerBlock,
	withParentBlock
);

/**
 * Add wrapper props to the blocks.
 *
 * @param {Object} BlockListBlock BlockListBlock element.
 * @return {Function} Handler.
 */
const addWrapperProps = ( BlockListBlock ) => {
	return wrapperWithSelect( ( props ) => {
		const { blockName, hasSelectedInnerBlock, attributes } = props;

		// If it's not an allowed block then lets return original;
		if ( -1 === ALLOWED_BLOCKS.indexOf( blockName ) ) {
			return <BlockListBlock { ...props } />;
		}

		let wrapperProps;

		// If we have an inner block selected let's add 'data-amp-selected=parent' to the wrapper.
		if (
			hasSelectedInnerBlock &&
			(
				'amp/amp-story-page' === blockName
			)
		) {
			wrapperProps = {
				...props.wrapperProps,
				'data-amp-selected': 'parent',
			};

			return <BlockListBlock { ...props } wrapperProps={ wrapperProps } />;
		}

		// If we have image caption or font-family set, add these to wrapper properties.
		wrapperProps = {
			...props.wrapperProps,
			'data-amp-image-caption': ( 'core/image' === blockName && ! attributes.ampShowImageCaption ) ? 'noCaption' : undefined,
			'data-font-family': attributes.ampFontFamily || undefined,
		};

		if ( ALLOWED_CHILD_BLOCKS.includes( blockName ) ) {
			let style = {
				top: attributes.positionTop,
				left: attributes.positionLeft,
			};
			if ( props.wrapperProps && props.wrapperProps.style ) {
				style = {
					...style,
					...props.wrapperProps.style
				};
			}
			wrapperProps = {
				...wrapperProps,
				style: style,
			};
		}

		return <BlockListBlock { ...props } style="background: yellow;" wrapperProps={ wrapperProps } />;
	} );
};

const dropBlockZoneWithSelect = compose(
	withParentBlock,
	withBlockName
);

/**
 * Filters DropBlockZone for the inner blocks of an AMP Story Page.
 *
 * @param {Object} OriginalBlockDropZone Original BlockDropZone component.
 * @return {Function} Handler.
 */
const filterDropBlockZone = ( OriginalBlockDropZone ) => {
	return dropBlockZoneWithSelect( ( props ) => {
		// If it's not an allowed child block, return original.
		if (
			! props.parentBlock ||
			'amp/amp-story-page' !== props.parentBlock.name ||
			! ALLOWED_CHILD_BLOCKS.includes( props.blockName ) ) {
			return <OriginalBlockDropZone { ...props } />;
		}

		return <BlockDropZone
			clientId={ props.clientId }
			rootClientId={ props.rootClientId }
		/>;
	} );
};

// These do not reliably work at domReady.
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/setBlockParent', setBlockParent );
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/filterEdit', withAnimationControls );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/filterEdit', withAmpStorySettings );
addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/addWrapperProps', addWrapperProps );
addFilter( 'blocks.getSaveContent.extraProps', 'ampStoryEditorBlocks/addExtraAttributes', addAMPExtraProps );
addFilter( 'editor.BlockDropZone', 'ampStoryEditorBlocks/filterBlockDropZone', filterDropBlockZone );
