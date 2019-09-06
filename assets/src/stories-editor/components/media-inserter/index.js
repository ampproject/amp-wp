/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { getBlockType, createBlock } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { withDispatch, withSelect } from '@wordpress/data';
import { DropdownMenu } from '@wordpress/components';
import { compose, ifCondition } from '@wordpress/compose';
import { __, sprintf } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import { processMedia } from '../../helpers';

const POPOVER_PROPS = {
	position: 'bottom right',
};

const MediaInserter = ( { insertBlock, updateBlock, canInsertBlockType, showInserter, mediaType } ) => {
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
			onClick: () => mediaPicker( __( 'Select or Upload Media', 'amp' ), 'image', updateBlock ),
			disabled: ! showInserter,
		},
		{
			title: videoTitle,
			icon: <BlockIcon icon={ 'media-video' } />,
			onClick: () => mediaPicker( __( 'Select or Upload Media', 'amp' ), 'video', updateBlock ),
			disabled: ! showInserter,
		},
	];

	for ( const block of blocks ) {
		if ( ! canInsertBlockType( block ) ) {
			continue;
		}

		const blockType = getBlockType( block );
		const item = {
			title: sprintf( __( 'Insert %s', 'amp' ), blockType.title ),
			onClick: () => insertBlock( block ),
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

MediaInserter.propTypes = {
	insertBlock: PropTypes.func.isRequired,
	updateBlock: PropTypes.func.isRequired,
	canInsertBlockType: PropTypes.func.isRequired,
	showInserter: PropTypes.bool.isRequired,
	mediaType: PropTypes.string.isRequired,
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

const applyWithSelect = withSelect( ( select ) => {
	const { getCurrentPage } = select( 'amp/story' );
	const { canInsertBlockType, getBlockListSettings, getBlock } = select( 'core/block-editor' );
	const { isReordering } = select( 'amp/story' );

	const currentPage = getCurrentPage();
	const block = getBlock( currentPage );
	const mediaType = block && block.attributes.mediaType ? block.attributes.mediaType : '';

	return {
		isReordering: isReordering(),
		canInsertBlockType: ( name ) => {
			// canInsertBlockType() alone is not enough, see https://github.com/WordPress/gutenberg/issues/14515
			const blockSettings = getBlockListSettings( currentPage );
			return canInsertBlockType( name, currentPage ) && blockSettings && blockSettings.allowedBlocks.includes( name );
		},
		// As used in <HeaderToolbar> component
		showInserter: select( 'core/edit-post' ).getEditorMode() === 'visual' && select( 'core/editor' ).getEditorSettings().richEditingEnabled,
		mediaType,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, props, { select } ) => {
	const { getCurrentPage } = select( 'amp/story' );
	const { getBlockOrder } = select( 'core/block-editor' );
	const { insertBlock } = dispatch( 'core/block-editor' );

	return {
		insertBlock: ( name ) => {
			const currentPage = getCurrentPage();
			const index = getBlockOrder( currentPage ).length;

			const insertedBlock = createBlock( name, {} );

			insertBlock( insertedBlock, index, currentPage );
		},
		updateBlock: ( media ) => {
			const clientId = getCurrentPage();
			const { updateBlockAttributes, selectBlock } = dispatch( 'core/block-editor' );

			if ( ! clientId ) {
				return;
			}

			const processed = processMedia( media );
			updateBlockAttributes( clientId, processed );
			selectBlock( clientId );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
	ifCondition( ( { isReordering } ) => ! isReordering ),
)( MediaInserter );
