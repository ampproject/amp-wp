/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { compose } from '@wordpress/compose';
import domReady from '@wordpress/dom-ready';
import { setDefaultBlockName } from '@wordpress/blocks';
import { select, subscribe } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { withAttributes, withParentBlock, withBlockName, withHasSelectedInnerBlock, withAmpStorySettings, withAnimationControls } from './components';
import { ALLOWED_BLOCKS, BLOCK_TAG_MAPPING } from './constants';
import { maybeEnqueueFontStyle } from './helpers';

const { getSelectedBlockClientId, getBlockOrder, getBlocksByClientId } = select( 'core/editor' );

// Ensure that the default block is page when no block is selected.
domReady( () => {
	setDefaultBlockName( 'amp/amp-story-page' );

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
	setDefaultBlockName( getSelectedBlockClientId() ? 'amp/amp-story-text' : 'amp/amp-story-page' );
} );

/**
 * Add AMP attributes to every allowed AMP Story block.
 *
 * @param {Object} settings Settings.
 * @param {string} name Block name.
 * @return {Object} Settings.
 */
const addAMPAttributes = ( settings, name ) => {
	if ( -1 === ALLOWED_BLOCKS.indexOf( name ) ) {
		return settings;
	}

	const addedAttributes = {};

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

	if ( -1 === ALLOWED_BLOCKS.indexOf( blockType.name ) ) {
		return props;
	}

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
	if ( -1 !== ALLOWED_BLOCKS.indexOf( name ) ) {
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
