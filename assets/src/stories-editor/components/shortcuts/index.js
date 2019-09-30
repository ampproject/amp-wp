/**
 * WordPress dependencies
 */
import { getBlockType, createBlock } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { withSelect, useSelect, useDispatch } from '@wordpress/data';
import { IconButton } from '@wordpress/components';
import { compose, ifCondition } from '@wordpress/compose';
import { useCallback } from '@wordpress/element';

const Shortcuts = () => {
	const {
		currentPage,
		index,
		canInsertBlockType,
		showInserter,
	} = useSelect( ( select ) => {
		const { getCurrentPage } = select( 'amp/story' );
		const { canInsertBlockType: canInsert, getBlockListSettings, getBlockOrder } = select( 'core/block-editor' );

		return {
			currentPage: getCurrentPage(),
			index: getBlockOrder( getCurrentPage() ).length,
			canInsertBlockType: ( name ) => {
				// canInsertBlockType() alone is not enough, see https://github.com/WordPress/gutenberg/issues/14515
				const blockSettings = getBlockListSettings( getCurrentPage() );
				return canInsert( name, getCurrentPage() ) && blockSettings && blockSettings.allowedBlocks.includes( name );
			},
			// As used in <HeaderToolbar> component
			showInserter: select( 'core/edit-post' ).getEditorMode() === 'visual' && select( 'core/editor' ).getEditorSettings().richEditingEnabled,
		};
	}, [] );

	const { insertBlock } = useDispatch( 'core/block-editor' );

	const onClick = useCallback( ( name ) => {
		const insertedBlock = createBlock( name, {} );

		insertBlock( insertedBlock, index, currentPage );
	}, [ currentPage, index, insertBlock ] );

	const blocks = [
		'amp/amp-story-text',
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
					onClick={ () => onClick( block ) }
					label={ blockType.title }
					labelPosition="bottom"
					disabled={ ! showInserter }
				/>
			);
		} )
	);
};

export default compose(
	withSelect( ( select ) => {
		const { isReordering } = select( 'amp/story' );

		return {
			isReordering: isReordering(),
		};
	} ),
	ifCondition( ( { isReordering } ) => ! isReordering ),
)( Shortcuts );
