/**
 * External dependencies
 */
import { every } from 'lodash';

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import domReady from '@wordpress/dom-ready';
import { select, subscribe, dispatch } from '@wordpress/data';
import { createBlock, getDefaultBlockName, setDefaultBlockName, getBlockTypes, unregisterBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import {
	withAmpStorySettings,
	withAnimationControls,
	withPageNumber,
	withWrapperProps,
	withActivePageState,
	withStoryBlockDropZone,
} from './components';

import {
	maybeEnqueueFontStyle,
	setBlockParent,
	addAMPAttributes,
	addAMPExtraProps,
	getTotalAnimationDuration,
	renderStoryComponents,
} from './helpers';

import { ALLOWED_BLOCKS, ALLOWED_TOP_LEVEL_BLOCKS, ALLOWED_CHILD_BLOCKS, MEDIA_INNER_BLOCKS } from './constants';

import store from './stores/amp-story';

const {
	getSelectedBlockClientId,
	getBlocksByClientId,
	getClientIdsWithDescendants,
	getBlockRootClientId,
	getBlockOrder,
	getBlock,
	getBlockAttributes,
} = select( 'core/editor' );

const {
	isReordering,
	getBlockOrder: getCustomBlockOrder,
	getCurrentPage,
	getAnimatedBlocks,
} = select( 'amp/story' );

const {
	moveBlockToPosition,
	updateBlockAttributes,
} = dispatch( 'core/editor' );

const {
	setCurrentPage,
	addAnimation,
	changeAnimationType,
	changeAnimationDuration,
	changeAnimationDelay,
} = dispatch( 'amp/story' );

/**
 * Initialize editor integration.
 */
domReady( () => {
	// Ensure that the default block is page when no block is selected.
	setDefaultBlockName( 'amp/amp-story-page' );

	// Remove all blocks that aren't whitelisted.
	const disallowedBlockTypes = getBlockTypes().filter( ( { name } ) => ! ALLOWED_BLOCKS.includes( name ) );

	for ( const blockType of disallowedBlockTypes ) {
		unregisterBlockType( blockType.name );
	}

	const allBlocks = getBlocksByClientId( getClientIdsWithDescendants() );

	// Set initially shown page.
	const firstPage = allBlocks.find( ( { name } ) => name === 'amp/amp-story-page' );
	setCurrentPage( firstPage ? firstPage.clientId : undefined );

	// Set initial animation order state for all child blocks.
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

		// Load all needed fonts.
		if ( block.attributes.ampFontFamily ) {
			maybeEnqueueFontStyle( block.attributes.ampFontFamily );
		}
	}

	renderStoryComponents();

	// Prevent WritingFlow component from focusing on last text field when clicking below the carousel.
	document.querySelector( '.editor-writing-flow__click-redirect' ).remove();
} );

const positionTopLimit = 75;
const positionTopHighest = 0;
const positionTopGap = 10;

/**
 * Set initial positioning if the selected block is an unmodified block.
 *
 * @param {string} clientId Block ID.
 */
function maybeSetInitialPositioning( clientId ) {
	const block = getBlock( clientId );

	if ( ! block || ! ALLOWED_CHILD_BLOCKS.includes( block.name ) ) {
		return;
	}

	const parentBlock = getBlock( getBlockRootClientId( clientId ) );
	// Short circuit if the top position is already set or the block has no parent.
	if ( 0 !== block.attributes.positionTop || ! parentBlock ) {
		return;
	}

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
}

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
function maybeUpdateAutoAdvanceAfterMedia( clientId ) {
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
}

let blockOrder = getBlockOrder();
let allBlocksWithChildren = getClientIdsWithDescendants();

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

	const newBlockOrder = getBlockOrder();
	const newlyAddedPages = newBlockOrder.find( ( block ) => ! blockOrder.includes( block ) );
	const deletedPages = blockOrder.filter( ( block ) => ! newBlockOrder.includes( block ) );

	if ( deletedPages.includes( getCurrentPage() ) ) {
		// Change current page if it has been deleted.
		const nextIndex = Math.max( 0, blockOrder.indexOf( getCurrentPage() ) - 1 );

		blockOrder = newBlockOrder;

		setCurrentPage( blockOrder[ nextIndex ] );
	}

	blockOrder = newBlockOrder;

	// If a new page has been inserted, make it the current one.
	if ( newlyAddedPages ) {
		setCurrentPage( newlyAddedPages );
	}

	for ( const block of allBlocksWithChildren ) {
		maybeSetInitialPositioning( block );
		maybeUpdateAutoAdvanceAfterMedia( block );
	}

	allBlocksWithChildren = getClientIdsWithDescendants();
} );

store.subscribe( () => {
	const editorBlockOrder = getBlockOrder();
	const customBlockOrder = getCustomBlockOrder();

	// The block order has changed, let's re-order.
	if ( ! isReordering() && customBlockOrder.length > 0 && editorBlockOrder !== customBlockOrder ) {
		for ( const [ index, page ] of customBlockOrder.entries() ) {
			moveBlockToPosition( page, '', '', index );
		}
	}

	const animatedBlocks = getAnimatedBlocks();

	// Update pages and blocks based on updated animation data.
	for ( const page in animatedBlocks ) {
		if ( ! animatedBlocks.hasOwnProperty( page ) || ! getBlock( page ) ) {
			continue;
		}

		const pageAttributes = getBlockAttributes( page );

		const animatedBlocksPerPage = animatedBlocks[ page ].filter( ( { id } ) => page === getBlockRootClientId( id ) );

		if ( [ 'auto', 'time' ].includes( pageAttributes.autoAdvanceAfter ) ) {
			const totalAnimationDuration = getTotalAnimationDuration( animatedBlocksPerPage );
			const totalAnimationDurationInSeconds = Math.ceil( totalAnimationDuration / 1000 );

			if ( 'time' === pageAttributes.autoAdvanceAfter ) {
				// Enforce minimum value for manually set time.
				if ( totalAnimationDurationInSeconds > pageAttributes.autoAdvanceAfterDuration ) {
					updateBlockAttributes( page, { autoAdvanceAfterDuration: totalAnimationDurationInSeconds } );
				}
			} else {
				updateBlockAttributes( page, { autoAdvanceAfterDuration: totalAnimationDurationInSeconds } );
			}
		}

		for ( const item of animatedBlocksPerPage ) {
			const { id, parent, animationType, duration, delay } = item;

			const parentBlock = parent ? getBlock( parent ) : undefined;

			updateBlockAttributes( id, {
				ampAnimationAfter: parentBlock ? parentBlock.attributes.anchor : undefined,
				ampAnimationType: animationType,
				ampAnimationDuration: duration,
				ampAnimationDelay: delay,
			} );
		}
	}
} );

addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/setBlockParent', setBlockParent );
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addAnimationControls', withAnimationControls );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addStorySettings', withAmpStorySettings );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addPageNumber', withPageNumber );
addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/withActivePageState', withActivePageState );
addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/addWrapperProps', withWrapperProps );
addFilter( 'blocks.getSaveContent.extraProps', 'ampStoryEditorBlocks/addExtraAttributes', addAMPExtraProps );
addFilter( 'editor.BlockDropZone', 'ampStoryEditorBlocks/withStoryBlockDropZone', withStoryBlockDropZone );
