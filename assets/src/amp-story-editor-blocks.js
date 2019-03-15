/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { select, subscribe, dispatch } from '@wordpress/data';
import { getDefaultBlockName, setDefaultBlockName, getBlockTypes, unregisterBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import {
	withAmpStorySettings,
	withAnimationControls,
	withPageNumber,
	withWrapperProps,
	withActivePageState,
	BlockNavigation,
	EditorCarousel,
	StoryControls,
	Shortcuts,
} from './components';
import { ALLOWED_BLOCKS } from './constants';
import { maybeEnqueueFontStyle, setBlockParent, addAMPAttributes, addAMPExtraProps, disableBlockDropZone } from './helpers';
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
	getAnimatedBlocks,
} = select( 'amp/story' );

const {
	moveBlockToPosition,
	updateBlockAttributes,
} = dispatch( 'core/editor' );

const {
	setCurrentPage,
	removePage,
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

	for ( const block of allBlocks ) {
		const page = getBlockRootClientId( block.clientId );

		// Set initial animation order state.
		if ( page ) {
			const { ampAnimationType, ampAnimationAfter, ampAnimationDuration, ampAnimationDelay } = block.attributes;
			const predecessor = allBlocks.find( ( b ) => b.attributes.anchor === ampAnimationAfter );

			addAnimation( page, block.clientId, predecessor ? predecessor.clientId : undefined );
			changeAnimationType( page, block.clientId, ampAnimationType );

			if ( ampAnimationType ) {
				changeAnimationDuration( page, block.clientId, ampAnimationDuration ? parseInt( ampAnimationDuration.replace( 'ms', '' ) ) : undefined );
				changeAnimationDelay( page, block.clientId, ampAnimationDelay ? parseInt( ampAnimationDelay.replace( 'ms', '' ) ) : undefined );
			}
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

/**
 * Add some additional elements needed to render our custom UI controls.
 */
function renderStoryComponents() {
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
}

let blockOrder = getBlockOrder();

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
	const deletedPages = blockOrder.filter( ( block ) => ! newBlockOrder.includes( block ) );
	const newlyAddedPages = newBlockOrder.find( ( block ) => ! blockOrder.includes( block ) );

	blockOrder = newBlockOrder;

	// If a new page has been inserted, make it the current one.
	if ( newlyAddedPages ) {
		setCurrentPage( newlyAddedPages );
	}

	// Remove stale data from store.
	for ( const oldPage of deletedPages ) {
		removePage( oldPage );
	}
} );

/**
 * Given a page and a list of animated blocks, calculates the total duration
 * of all animations.
 *
 * Traverses through the animation order tree level by level.
 *
 * @param {string} page           Page client ID.
 * @param {Array}  animatedBlocks List of animated blocks for that page.
 *
 * @return {number} Total animation duration time.
 */
const getTotalAnimationDuration = ( page, animatedBlocks ) => {
	const getLongestAnimation = ( parentBlockId ) => {
		return animatedBlocks
			.filter( ( { parent } ) => parent === parentBlockId )
			.map( ( { id, duration, delay } ) => {
				if ( page !== getBlockRootClientId( id ) ) {
					return 0;
				}

				const animationDelay = delay ? parseInt( delay ) : 0;
				const animationDuration = duration ? parseInt( duration ) : 0;

				return animationDelay + animationDuration;
			} )
			.reduce( ( max, current ) => Math.max( max, current ), 0 );
	};

	const levels = [ ...new Set( animatedBlocks.map( ( { parent } ) => parent ) ) ];

	return levels.map( getLongestAnimation ).reduce( ( sum, duration ) => sum + duration, 0 );
};

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
		if ( ! animatedBlocks.hasOwnProperty( page ) ) {
			continue;
		}

		const pageAttributes = getBlockAttributes( page );

		if ( 'auto' === pageAttributes.autoAdvanceAfter ) {
			const totalAnimationDuration = getTotalAnimationDuration( page, animatedBlocks[ page ] );
			// @todo Fine tune this?
			const totalAnimationDurationInSeconds = Math.ceil( totalAnimationDuration / 1000 );

			if ( totalAnimationDurationInSeconds !== pageAttributes.autoAdvanceAfterDuration ) {
				updateBlockAttributes( page, { autoAdvanceAfterDuration: totalAnimationDurationInSeconds } );
			}
		}

		for ( const item of animatedBlocks[ page ] ) {
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
addFilter( 'editor.BlockDropZone', 'ampStoryEditorBlocks/disableBlockDropZone', disableBlockDropZone );
