/**
 * WordPress dependencies
 */
import { getBlockType, createBlock } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { withDispatch, withSelect } from '@wordpress/data';
import { IconButton } from '@wordpress/components';
import { compose } from '@wordpress/compose';

const Shortcuts = ( { insertBlock, canInsertBlockType } ) => {
	const blocks = [
		'amp/amp-story-text',
		'core/image',
		'amp/amp-story-cta',
	];

	return (
		blocks.map( ( block ) => {
			if ( ! canInsertBlockType( block ) ) {
				return null;
			}

			const blockType = getBlockType( block );

			return (
				<IconButton
					key={ block }
					icon={ <BlockIcon icon={ blockType.icon } /> }
					onClick={ () => insertBlock( block ) }
					label={ blockType.title }
					labelPosition="bottom"
				/>
			);
		} )
	);
};

const applyWithSelect = withSelect( ( select ) => {
	const { getCurrentPage } = select( 'amp/story' );
	const { canInsertBlockType, getBlockListSettings } = select( 'core/editor' );

	return {
		canInsertBlockType: ( name ) => {
			// canInsertBlockType() alone is not enough, see https://github.com/WordPress/gutenberg/issues/14515
			const blockSettings = getBlockListSettings( getCurrentPage() );
			return canInsertBlockType( name, getCurrentPage() ) && blockSettings && blockSettings.allowedBlocks.includes( name );
		},
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, props, { select } ) => {
	const { getCurrentPage } = select( 'amp/story' );
	const { getBlockOrder } = select( 'core/editor' );
	const { insertBlock } = dispatch( 'core/editor' );

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
)( Shortcuts );
