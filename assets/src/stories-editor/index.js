/**
 * External dependencies
 */
import { has } from 'lodash';

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';
import { select, subscribe, dispatch } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { registerFormatType } from '@wordpress/rich-text';
import {
	getDefaultBlockName,
	setDefaultBlockName,
	getBlockTypes,
	unregisterBlockType,
	registerBlockType,
	registerBlockStyle,
	unregisterBlockStyle,
} from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import {
	withCroppedFeaturedImage,
	withEnforcedFileType,
} from '../common/components';
import {
	withAmpStorySettings,
	withPageNumber,
	withEditFeaturedImage,
	withCustomVideoBlockEdit,
	withStoryFeaturedImageNotice,
	withWrapperProps,
	withActivePageState,
	withStoryBlockDropZone,
	withCallToActionValidation,
	withEnforcedVideoUploadType,
} from './components';
import {
	maybeEnqueueFontStyle,
	setBlockParent,
	filterBlockAttributes,
	filterBlockTransforms,
	addAMPAttributes,
	addAMPExtraProps,
	deprecateCoreBlocks,
	getTotalAnimationDuration,
	renderStoryComponents,
	maybeInitializeAnimations,
	maybeRemoveMediaCaption,
	maybeSetInitialPositioning,
	maybeSetInitialSize,
	maybeSetTagName,
	maybeUpdateAutoAdvanceAfterMedia,
	maybeRemoveDeprecatedSetting,
	wrapBlocksInGridLayer,
	getMinimumStoryPosterDimensions,
	maybeAddMissingAnchor,
} from './helpers';

import { ALLOWED_BLOCKS } from './constants';

import store from './store';

const {
	getSelectedBlock,
	getBlocksByClientId,
	getClientIdsWithDescendants,
	getBlockRootClientId,
	getBlockOrder,
	getBlock,
	getBlocks,
	getBlockAttributes,
} = select( 'core/block-editor' );

const {
	moveBlockToPosition,
	updateBlockAttributes,
} = dispatch( 'core/block-editor' );

const { getEditorMode, isFeatureActive } = select( 'core/edit-post' );
const { toggleFeature } = dispatch( 'core/edit-post' );

const {
	isReordering,
	getBlockOrder: getCustomBlockOrder,
	getCurrentPage,
	getAnimatedBlocks,
} = select( 'amp/story' );

const {	setCurrentPage } = dispatch( 'amp/story' );

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
		// Load all needed fonts.
		if ( block.attributes.ampFontFamily ) {
			maybeEnqueueFontStyle( block.attributes.ampFontFamily );
		}
		maybeRemoveDeprecatedSetting( block );
	}

	// Enforce fixed toolbar.
	if ( ! isFeatureActive( 'fixedToolbar' ) ) {
		toggleFeature( 'fixedToolbar' );
	}

	renderStoryComponents();

	for ( const roundedBlock of [ 'amp/amp-story-text', 'amp/amp-story-post-author', 'amp/amp-story-post-date', 'amp/amp-story-post-title' ] ) {
		registerBlockStyle( roundedBlock, {
			name: 'rounded',
			label: __( 'Rounded', 'amp' ),
		} );
	}

	registerBlockStyle( 'amp/amp-story-text', {
		name: 'half-rounded',
		label: __( 'Half Rounded', 'amp' ),
	} );

	registerBlockStyle( 'core/image', {
		name: 'rounded',
		label: __( 'Rounded', 'amp' ),
	} );

	registerBlockStyle( 'core/quote', {
		name: 'white',
		label: __( 'White', 'amp' ),
	} );

	unregisterBlockStyle( 'core/quote', 'large' );
} );

let blockOrder = getBlockOrder();
let allBlocksWithChildren = getClientIdsWithDescendants();

let editorMode = getEditorMode();

let selectedBlock;

subscribe( async () => {
	maybeInitializeAnimations();

	const defaultBlockName = getDefaultBlockName();
	const newSelectedBlock = getSelectedBlock();

	// Switch default block depending on context
	if ( newSelectedBlock ) {
		if ( 'amp/amp-story-page' === newSelectedBlock.name && 'amp/amp-story-page' !== defaultBlockName ) {
			setDefaultBlockName( 'amp/amp-story-page' );
		} else if ( 'amp/amp-story-page' !== newSelectedBlock.name && 'amp/amp-story-text' !== defaultBlockName ) {
			setDefaultBlockName( 'amp/amp-story-text' );
		}
	} else if ( 'amp/amp-story-page' !== defaultBlockName ) {
		setDefaultBlockName( 'amp/amp-story-page' );
	}

	if ( selectedBlock !== newSelectedBlock ) {
		const editPostLayout = document.querySelector( '.edit-post-layout' );
		if ( editPostLayout ) {
			editPostLayout.setAttribute( 'data-block-name', newSelectedBlock ? newSelectedBlock.name : '' );
		}
	}

	selectedBlock = newSelectedBlock;

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
		maybeRemoveMediaCaption( block );
		maybeUpdateAutoAdvanceAfterMedia( block );
		maybeSetTagName( block );
		maybeSetInitialSize( block );
	}

	allBlocksWithChildren = getClientIdsWithDescendants();

	// Re-add controls when switching back from code to visual editor.
	const newEditorMode = getEditorMode();
	if ( 'visual' === newEditorMode && newEditorMode !== editorMode ) {
		while ( ! document.querySelector( '.editor-block-list__layout' ) ) {
			await new Promise( ( r ) => setTimeout( r, 200 ) ); // eslint-disable-line no-await-in-loop
		}

		renderStoryComponents();
	}

	editorMode = newEditorMode;
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

		for ( const item of animatedBlocksPerPage ) {
			const { id, parent, animationType, duration, delay } = item;

			// If the parent block wasn't saved previously it doesn't have an anchor and we have to set it.
			maybeAddMissingAnchor( parent );
			const parentBlock = parent ? getBlock( parent ) : undefined;

			updateBlockAttributes( id, {
				ampAnimationAfter: parentBlock ? parentBlock.attributes.anchor : undefined,
				ampAnimationType: animationType,
				ampAnimationDuration: duration,
				ampAnimationDelay: delay,
			} );
		}
	}

	/*
	 * Prevent an issue where duplicated page blocks had the same anchor attribute, and didn't look right on the front-end.
	 * @see https://github.com/ampproject/amp-wp/issues/2510
	 */
	const conditionallyUpdateAnchor = ( block ) => {
		if ( ! has( block, [ 'attributes', 'anchor' ] ) ) {
			return;
		}

		if ( block.attributes.anchor !== block.clientId ) {
			updateBlockAttributes( block.clientId, { anchor: block.clientId } );
		}
	};

	const blocksToVerifyAnchor = getBlocks();
	for ( const blockToVerifyAnchor of blocksToVerifyAnchor ) {
		conditionallyUpdateAnchor( blockToVerifyAnchor );
	}
} );

const plugins = require.context( './plugins', true, /.*\.js$/ );

plugins.keys().forEach( ( modulePath ) => {
	const { name, render, isActive = true } = plugins( modulePath );

	if ( isActive ) {
		registerPlugin( name, { render } );
	}
} );

addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/setBlockParent', setBlockParent );
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/filterBlockTransforms', filterBlockTransforms );
addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/deprecateCoreBlocks', deprecateCoreBlocks );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addStorySettings', withAmpStorySettings );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addPageNumber', withPageNumber );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addEditFeaturedImage', withEditFeaturedImage );
addFilter( 'editor.BlockEdit', 'ampEditorBlocks/addVideoBlockPreview', withCustomVideoBlockEdit, 9 );
addFilter( 'editor.PostFeaturedImage', 'ampStoryEditorBlocks/addFeaturedImageNotice', withStoryFeaturedImageNotice );
addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/withActivePageState', withActivePageState );
addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/addWrapperProps', withWrapperProps );
addFilter( 'editor.MediaUpload', 'ampStoryEditorBlocks/addEnforcedFileType', ( InitialMediaUpload ) => withEnforcedFileType( InitialMediaUpload ) );
addFilter( 'editor.MediaUpload', 'ampStoryEditorBlocks/addCroppedFeaturedImage', ( InitialMediaUpload ) => withCroppedFeaturedImage( InitialMediaUpload, getMinimumStoryPosterDimensions() ) );
addFilter( 'editor.MediaPlaceholder', 'ampStoryEditorBlocks/addEnforcedVideoUploadType', withEnforcedVideoUploadType );
addFilter( 'blocks.getSaveContent.extraProps', 'ampStoryEditorBlocks/addExtraAttributes', addAMPExtraProps );
addFilter( 'blocks.getSaveElement', 'ampStoryEditorBlocks/wrapBlocksInGridLayer', wrapBlocksInGridLayer );
addFilter( 'editor.BlockDropZone', 'ampStoryEditorBlocks/withStoryBlockDropZone', withStoryBlockDropZone );
addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/withCallToActionValidation', withCallToActionValidation );
addFilter( 'blocks.getBlockAttributes', 'ampStoryEditorBlocks/filterBlockAttributes', filterBlockAttributes );

const blocks = require.context( './blocks', true, /(?<!test\/)index\.js$/ );

// Block types need to be register *after* all the filters have been applied.
blocks.keys().forEach( ( modulePath ) => {
	const { name, settings } = blocks( modulePath );
	registerBlockType( name, settings );
} );

const formats = require.context( './formats', true, /(?<!test\/)index\.js$/ );

formats.keys().sort( ( a, b ) => ( a.priority > b.priority ) ? 1 : -1 ).forEach( ( modulePath ) => {
	const { name, settings } = formats( modulePath );
	registerFormatType( name, settings );
} );
