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
import { getDefaultBlockName, setDefaultBlockName } from '@wordpress/blocks';
import { select, subscribe, dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { withAttributes, withParentBlock, withBlockName, withHasSelectedInnerBlock, withAmpStorySettings, withAnimationControls } from './components';
import { ALLOWED_BLOCKS, BLOCK_TAG_MAPPING } from './constants';
import { maybeEnqueueFontStyle } from './helpers';
import { store } from './stores/amp-story';

const { getSelectedBlockClientId, getBlockOrder, getBlocksByClientId, getBlock } = select( 'core/editor' );
const { getAnimationOrder } = select( 'amp/story' );
const { addAnimation, removePage } = dispatch( 'amp/story' );

domReady( () => {
	setDefaultBlockName( 'amp/amp-story-page' );

	// Set initial animation order state.
	getBlocksByClientId( getBlockOrder() )
		.filter( ( block ) => block.name === 'amp/amp-story-page' )
		.map( ( page ) => {
			const blocks = getBlocksByClientId( getBlockOrder( page.clientId ) );

			blocks.filter( ( block ) => block.attributes.ampAnimationType )
				.map( ( block ) => {
					const ampAnimationAfter = block.attributes.ampAnimationAfter;
					const predecessor = blocks.find( ( b ) => b.attributes.anchor === ampAnimationAfter );

					addAnimation( page.clientId, block.clientId, predecessor ? predecessor.clientId : undefined );
				} );
		} );

	// Load all needed fonts.
	getBlocksByClientId( getBlockOrder() )
		.filter( ( block ) => block.name === 'amp/amp-story-page' )
		.map( ( page ) => {
			getBlocksByClientId( getBlockOrder( page.clientId ) )
				.filter( ( block ) => block.attributes.ampFontFamily )
				.map( ( block ) => {
					maybeEnqueueFontStyle( block.attributes.ampFontFamily );
				} );
		} );
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
	Object.keys( getAnimationOrder() ).map( ( page ) => {
		if ( ! getBlock( page ) ) {
			removePage( store.getState(), page );
		}
	} );
} );

/**
 * Add AMP attributes to every allowed AMP Story block.
 *
 * @param {Object} settings Settings.
 * @param {string} name Block name.
 * @return {Object} Settings.
 */
const addAMPAttributes = ( settings, name ) => {
	const addedAttributes = {
		anchor: {
			type: 'string',
			source: 'attribute',
			attribute: 'id',
			selector: '*',
		},
	};

	if ( ! ALLOWED_BLOCKS.includes( name ) ) {
		settings.attributes = {
			...settings.attributes,
			...addedAttributes,
		};

		return settings;
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

	// Disable anchor support as we auto-generate IDs.
	settings.supports = settings.supports || {};
	settings.supports.anchor = false;

	settings.attributes = settings.attributes || {};
	settings.attributes = {
		...settings.attributes,
		...addedAttributes,
	};

	return settings;
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

	if ( ! ALLOWED_BLOCKS.includes( blockType.name ) ) {
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
	if ( ! ALLOWED_BLOCKS.includes( name ) ) {
		// Only amp/amp-story-page blocks can be on the top level.
		return {
			...props,
			parent: [ 'amp/amp-story-page' ],
		};
	}

	if ( -1 === name.indexOf( 'amp/amp-story-page' ) ) {
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

		return <BlockListBlock { ...props } wrapperProps={ wrapperProps } />;
	} );
};

// These do not reliably work at domReady.
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/setBlockParent', setBlockParent );
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/filterEdit', withAnimationControls );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/filterEdit', withAmpStorySettings );
addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/addWrapperProps', addWrapperProps );
addFilter( 'blocks.getSaveContent.extraProps', 'ampStoryEditorBlocks/addExtraAttributes', addAMPExtraProps );
