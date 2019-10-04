/**
 * WordPress dependencies
 */
import { getBlockType, createBlock } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
import { DropdownMenu } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { processMedia, useIsBlockAllowedOnPage } from '../../helpers';
import { IMAGE_BACKGROUND_TYPE } from '../../constants';

const POPOVER_PROPS = {
	position: 'bottom right',
};

const MediaInserter = () => {
	const {
		currentPage,
		blockOrder,
		showInserter,
		mediaType,
		allowedVideoMimeTypes,
	} = useSelect( ( select ) => {
		const { getCurrentPage } = select( 'amp/story' );
		const { getBlock, getBlockOrder } = select( 'core/block-editor' );
		const { getSettings } = select( 'amp/story' );

		const _currentPage = getCurrentPage();
		const block = getBlock( _currentPage );

		return {
			currentPage: _currentPage,
			blockOrder: getBlockOrder( _currentPage ),
			// As used in <HeaderToolbar> component
			showInserter: select( 'core/edit-post' ).getEditorMode() === 'visual' && select( 'core/editor' ).getEditorSettings().richEditingEnabled,
			mediaType: block && block.attributes.mediaType ? block.attributes.mediaType : '',
			allowedVideoMimeTypes: getSettings().allowedVideoMimeTypes,
		};
	}, [] );

	const isBlockAllowedOnPage = useIsBlockAllowedOnPage();

	const { insertBlock, updateBlockAttributes, selectBlock } = useDispatch( 'core/block-editor' );

	const insertBlockOnPage = useCallback( ( name ) => {
		const index = blockOrder.length;

		const insertedBlock = createBlock( name, {} );

		insertBlock( insertedBlock, index, currentPage );
	}, [ blockOrder, currentPage, insertBlock ] );

	const updateBlock = useCallback( ( media ) => {
		if ( ! currentPage ) {
			return;
		}

		const processed = processMedia( media );
		updateBlockAttributes( currentPage, processed );
		selectBlock( currentPage );
	}, [ currentPage, selectBlock, updateBlockAttributes ] );

	const isReordering = useSelect( ( select ) => select( 'amp/story' ).isReordering(), [] );

	if ( isReordering ) {
		return null;
	}

	const blocks = [
		'core/video',
		'core/image',
	];

	const imageTitle = 'image' === mediaType ? __( 'Update Background Image', 'amp' ) : __( 'Insert Background Image', 'amp' );
	const videoTitle = 'video' === mediaType ? __( 'Update Background Video', 'amp' ) : __( 'Insert Background Video', 'amp' );

	const dropDownOptions = [
		{
			title: imageTitle,
			icon: <BlockIcon icon={ 'format-image' } />,
			onClick: () => mediaPicker( __( 'Select or Upload Media', 'amp' ), IMAGE_BACKGROUND_TYPE, updateBlock ),
			disabled: ! showInserter,
		},
		{
			title: videoTitle,
			icon: <BlockIcon icon={ 'media-video' } />,
			onClick: () => mediaPicker( __( 'Select or Upload Media', 'amp' ), allowedVideoMimeTypes, updateBlock ),
			disabled: ! showInserter,
		},
	];

	for ( const block of blocks ) {
		if ( ! isBlockAllowedOnPage( block, currentPage ) ) {
			continue;
		}

		const blockType = getBlockType( block );
		const item = {
			title: sprintf( __( 'Insert %s', 'amp' ), blockType.title ),
			onClick: () => insertBlockOnPage( block ),
			disabled: ! showInserter,
			icon: <BlockIcon icon={ blockType.icon } />,
		};

		dropDownOptions.unshift( item );
	}

	return (
		<DropdownMenu
			icon="admin-media"
			label={ __( 'Insert Media', 'amp' ) }
			className="amp-story-media-inserter-dropdown"
			controls={ dropDownOptions }
			hasArrowIndicator={ true }
			popoverProps={ POPOVER_PROPS }
			toggleProps={
				{ labelPosition: 'bottom' }
			}
		/>
	);
};

const mediaPicker = ( dialogTitle, mediaType, updateBlock ) => {
	// Create the media frame.
	const fileFrame = wp.media( {
		title: dialogTitle,
		button: {
			text: __( 'Select', 'amp' ),
		},
		multiple: false,
		library: {
			type: mediaType,
		},
	} );
	let attachment;

	// When an image is selected, run a callback.
	fileFrame.on( 'select', () => {
		attachment = fileFrame.state().get( 'selection' ).first().toJSON();
		updateBlock( attachment );
	} );

	// Finally, open the modal
	fileFrame.open();
};

export default MediaInserter;
