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
import { IMAGE_BACKGROUND_TYPE, VIDEO_BACKGROUND_TYPE } from '../../constants';

const MediaInsider = ( { insertBlock, updateBlock, canInsertBlockType, showInserter } ) => {
	const blocks = [
		'core/video',
		'core/image',
	];

	const dropDownOptions = [
		{
			title: __( 'Insert background image', 'amp' ),
			icon: 'format-image',
			onClick: () => mediaPicker( __( 'Select background image', 'amp' ), 'image', updateBlock ),
			disabled: ! showInserter,
		},
		{
			title: __( 'Insert background video', 'amp' ),
			icon: 'media-video',
			onClick: () => mediaPicker( __( 'Select background video', 'amp' ), 'video', updateBlock ),
			disabled: ! showInserter,
		},
	];

	blocks.map( ( block ) => {
		if ( ! canInsertBlockType( block ) ) {
			return null;
		}

		const blockType = getBlockType( block );
		const item = {
			title: sprintf( __( 'Insert %s', 'amp' ), blockType.title ),
			onClick: () => insertBlock( block ),
			disabled: ! showInserter,
			icon: <BlockIcon icon={ blockType.icon } />,
		};
		dropDownOptions.unshift( item );
		return null;
	} );

	return (
		<DropdownMenu
			icon="admin-media"
			label={ __( 'Select media', 'amp' ) }
			controls={ dropDownOptions }
			hasArrowIndicator={ true }
			toggleProps={
				{ labelPosition: 'bottom' }
			}
		/>
	);
};

MediaInsider.propTypes = {
	insertBlock: PropTypes.func.isRequired,
	updateBlock: PropTypes.func.isRequired,
	canInsertBlockType: PropTypes.func.isRequired,
	showInserter: PropTypes.bool.isRequired,
};

const mediaPicker = ( dialogTitle, mediaType, updateBlock ) => {
	// Create the media frame.
	const fileFrame = wp.media( {
		title: dialogTitle,
		button: {
			text: __( 'Use as background', 'amp' ),
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
	const { canInsertBlockType, getBlockListSettings } = select( 'core/block-editor' );
	const { isReordering } = select( 'amp/story' );

	return {
		isReordering: isReordering(),
		canInsertBlockType: ( name ) => {
			// canInsertBlockType() alone is not enough, see https://github.com/WordPress/gutenberg/issues/14515
			const blockSettings = getBlockListSettings( getCurrentPage() );
			return canInsertBlockType( name, getCurrentPage() ) && blockSettings && blockSettings.allowedBlocks.includes( name );
		},
		// As used in <HeaderToolbar> component
		showInserter: select( 'core/edit-post' ).getEditorMode() === 'visual' && select( 'core/editor' ).getEditorSettings().richEditingEnabled,
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
			const { updateBlockAttributes } = dispatch( 'core/block-editor' );

			if ( ! clientId ) {
				return;
			}

			if ( ! media || ! media.url ) {
				updateBlockAttributes( clientId,
					{
						mediaUrl: undefined,
						mediaId: undefined,
						mediaType: undefined,
						mediaAlt: undefined,
						poster: undefined,
					}
				);
				return;
			}

			let mediaType;

			// For media selections originated from a file upload.
			if ( media.media_type ) {
				if ( media.media_type === VIDEO_BACKGROUND_TYPE ) {
					mediaType = VIDEO_BACKGROUND_TYPE;
				} else {
					mediaType = IMAGE_BACKGROUND_TYPE;
				}
			} else {
				// For media selections originated from existing files in the media library.
				if (
					media.type !== IMAGE_BACKGROUND_TYPE &&
					media.type !== VIDEO_BACKGROUND_TYPE
				) {
					return;
				}

				mediaType = media.type;
			}

			const mediaAlt = media.alt || media.title;
			const mediaUrl = media.url;
			const poster = VIDEO_BACKGROUND_TYPE === mediaType && media.image && media.image.src !== media.icon ? media.image.src : undefined;
			updateBlockAttributes( clientId, {
				mediaUrl,
				mediaId: media.id,
				mediaType,
				mediaAlt,
				poster,
			} );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
	ifCondition( ( { isReordering } ) => ! isReordering ),
)( MediaInsider );
