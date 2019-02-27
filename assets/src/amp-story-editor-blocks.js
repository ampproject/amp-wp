/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { compose, createHigherOrderComponent } from '@wordpress/compose';
import domReady from '@wordpress/dom-ready';
import { getDefaultBlockName, setDefaultBlockName } from '@wordpress/blocks';
import { select, subscribe, dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { withAttributes, withParentBlock, withBlockName, withHasSelectedInnerblock, withAmpStorySettings } from './components';
import { ALLOWED_BLOCKS, BLOCK_TAG_MAPPING } from './helpers';
import './stores/amp-story';

const { getSelectedBlockClientId, getBlockOrder, getBlocksByClientId, getBlock } = select( 'core/editor' );
const { updateBlockAttributes } = dispatch( 'core/editor' );
const { getAnimationPredecessor } = select( 'amp/story' );
const { addAnimation } = dispatch( 'amp/story' );

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
} );

subscribe( () => {
	const defaultBlockName = getDefaultBlockName();
	const selectedBlockClientId = getSelectedBlockClientId();

	// Ensure that the default block is page when no block is selected.
	if ( selectedBlockClientId && 'core/paragraph' !== defaultBlockName ) {
		setDefaultBlockName( 'core/paragraph' );
	} else if ( ! selectedBlockClientId && 'amp/amp-story-page' !== defaultBlockName ) {
		setDefaultBlockName( 'amp/amp-story-page' );
	}

	// Keep animation order in order.
	// Todo: First check if animation order has actually changed.
	getBlocksByClientId( getBlockOrder() )
		.filter( ( block ) => block.name === 'amp/amp-story-page' )
		.map( ( page ) => {
			const blocks = getBlocksByClientId( getBlockOrder( page.clientId ) );

			blocks.map( ( block ) => {
				const predecessor = getAnimationPredecessor( page.clientId, block.clientId );
				const predecessorBlock = predecessor ? getBlock( predecessor ) : undefined;

				updateBlockAttributes( block.clientId, { ampAnimationAfter: predecessorBlock ? predecessorBlock.attributes.anchor : undefined } );
			} );
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

	// Lets add font family to the text blocks.
	if ( 'core/paragraph' === name || 'core/heading' === name ) {
		addedAttributes.ampFontFamily = {
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
	withHasSelectedInnerblock,
	withParentBlock
);

/**
 * Add wrapper props to the blocks.
 *
 * @param {Object} BlockListBlock BlockListBlock element.
 * @return {Function} Handler.
 */
const addWrapperProps = createHigherOrderComponent(
	( BlockListBlock ) => {
		return wrapperWithSelect( ( props ) => {
			const { blockName, hasSelectedInnerBlock, attributes } = props;

			// If we have an inner block selected let's add 'data-amp-selected=parent' to the wrapper.
			if (
				hasSelectedInnerBlock &&
				(
					'amp/amp-story-page' === blockName
				)
			) {
				return <BlockListBlock { ...props } data-amp-selected={ 'parent' } />;
			}

			// If we got this far and it's not an allowed inner block then lets return original.
			if ( -1 === ALLOWED_BLOCKS.indexOf( blockName ) ) {
				return <BlockListBlock { ...props } />;
			}

			return <BlockListBlock
				{ ...props }
				data-amp-image-caption={ ( 'core/image' === blockName && ! attributes.ampShowImageCaption ) ? 'noCaption' : undefined }
				data-font-family={ attributes.ampFontFamily || undefined }
			/>;
		} );
	},
	'addWrapperProps'
);

// These do not reliably work at domReady.
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/setBlockParent', setBlockParent );
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/filterEdit', withAmpStorySettings );
addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/addWrapperProps', addWrapperProps );
addFilter( 'blocks.getSaveContent.extraProps', 'ampStoryEditorBlocks/addExtraAttributes', addAMPExtraProps );
