/**
 * WordPress dependencies
 */
import { getBlockType, createBlock } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { withDispatch } from '@wordpress/data';
import { IconButton } from '@wordpress/components';

const Shortcuts = ( { insertBlock } ) => {
	const blocks = [
		'amp/amp-story-text',
		'core/image',
		'amp/amp-story-title',
	];

	return (
		blocks.map( ( block ) => {
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

export default withDispatch( ( dispatch, props, { select } ) => {
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
} )( Shortcuts );
