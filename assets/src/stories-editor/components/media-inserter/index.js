/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { getBlockType, createBlock } from '@wordpress/blocks';
import { BlockIcon, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { withDispatch, withSelect } from '@wordpress/data';
import { DropdownMenu, MenuGroup, MenuItem } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { compose, ifCondition } from '@wordpress/compose';
import { __, sprintf } from '@wordpress/i18n';

const MediaInsider = ( { insertBlock, canInsertBlockType, showInserter } ) => {
	const instructions = <p>{ __( 'To edit the background image or video, you need permission to upload media.', 'amp' ) }</p>;
	const mediaId = 0;
	return (
		<DropdownMenu
			icon="admin-media"
			label="Select a media"
		>
			{ ( { onClose } ) => (
				<Fragment>
					<MenuGroup>
						<MenuItem
							icon="image"
							onClick={ () => insertBlock( 'core/image' )  }
						>
							{ __( 'Insert image', 'amp' ) }
						</MenuItem>
						<MediaUploadCheck fallback={ instructions }>
							<MediaUpload
								onSelect={ ( media ) => console.log( 'selected ' + media.length ) }
								value={ mediaId }
								render={ ( { open } ) => (
								<MenuItem
									icon="format-image"
									onClick={ open }
										>
										{ __( 'Insert background image', 'amp' ) }
								</MenuItem>
							) }
							/>
						</MediaUploadCheck>
						<MediaUploadCheck fallback={ instructions }>
							<MediaUpload
								onSelect={ ( media ) => console.log( 'selected ' + media.length ) }
								allowedTypes={ [ 'video' ] }
								value={ mediaId }
								render={ ( { open } ) => (
									<MenuItem
										icon="media-video"
										onClick={ open }
									>
										{ __( 'Insert background video', 'amp' ) }
									</MenuItem>
								) }
							/>
						</MediaUploadCheck>

					</MenuGroup>
				</Fragment>
			) }
		</DropdownMenu>
	);
};

MediaInsider.propTypes = {
	insertBlock: PropTypes.func.isRequired,
	canInsertBlockType: PropTypes.func.isRequired,
	showInserter: PropTypes.bool.isRequired,
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
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
	ifCondition( ( { isReordering } ) => ! isReordering ),
)( MediaInsider );
