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
} from './components';
import { ALLOWED_BLOCKS } from './constants';
import { maybeEnqueueFontStyle, setBlockParent, addAMPAttributes, addAMPExtraProps, disableBlockDropZone } from './helpers';
import { store } from './stores/amp-story';

/**
 * Initialize editor integration.
 */
domReady( () => {
	const { getBlocksByClientId, getClientIdsWithDescendants, getBlockRootClientId } = select( 'core/editor' );
	const { addAnimation, setCurrentPage } = dispatch( 'amp/story' );

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
			const ampAnimationAfter = block.attributes.ampAnimationAfter;
			const predecessor = allBlocks.find( ( b ) => b.attributes.anchor === ampAnimationAfter );

			addAnimation( page, block.clientId, predecessor ? predecessor.clientId : undefined );
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

	if ( ! editorBlockList ) {
		return;
	}

	const blockNavigation = document.createElement( 'div' );
	blockNavigation.id = 'amp-root-navigation';

	const editorCarousel = document.createElement( 'div' );
	editorCarousel.id = 'amp-story-editor-carousel';

	const storyControls = document.createElement( 'div' );
	storyControls.id = 'amp-story-controls';

	/**
	 * The intended layout is as follows:
	 *
	 * - Story controls
	 * - Block list
	 * - Carousel controls
	 */
	editorBlockList.parentNode.insertBefore( storyControls, editorBlockList );
	editorBlockList.parentNode.insertBefore( blockNavigation, editorBlockList.nextSibling );
	editorBlockList.parentNode.insertBefore( editorCarousel, editorBlockList.nextSibling );

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

const { getBlockOrder } = select( 'core/editor' );

let blockOrder = getBlockOrder();

subscribe( () => {
	const { getSelectedBlockClientId, getBlock } = select( 'core/editor' );
	const { setCurrentPage, removePage } = dispatch( 'amp/story' );
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

const { isReordering, getBlockOrder: getCustomBlockOrder } = select( 'amp/story' );
const { moveBlockToPosition } = dispatch( 'core/editor' );

store.subscribe( () => {
	const editorBlockOrder = getBlockOrder();
	const customBlockOrder = getCustomBlockOrder();

	// The block order was changed manually, let's do the re-order.
	if ( ! isReordering() && customBlockOrder.length > 0 && editorBlockOrder !== customBlockOrder ) {
		for ( const [ index, page ] of customBlockOrder.entries() ) {
			moveBlockToPosition( page, '', '', index );
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
