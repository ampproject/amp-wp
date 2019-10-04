/**
 * WordPress dependencies
 */
import { getBlockType, createBlock } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { IconButton } from '@wordpress/components';
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useIsBlockAllowedOnPage } from '../../helpers';

const Shortcuts = () => {
	const {
		currentPage,
		index,
		showInserter,
	} = useSelect( ( select ) => {
		const { getCurrentPage } = select( 'amp/story' );
		const { getBlockOrder } = select( 'core/block-editor' );

		return {
			currentPage: getCurrentPage(),
			index: getBlockOrder( getCurrentPage() ).length,
			// As used in <HeaderToolbar> component
			showInserter: select( 'core/edit-post' ).getEditorMode() === 'visual' && select( 'core/editor' ).getEditorSettings().richEditingEnabled,
		};
	}, [] );

	const isBlockAllowedOnPage = useIsBlockAllowedOnPage();

	const { insertBlock } = useDispatch( 'core/block-editor' );

	const onClick = useCallback( ( name ) => {
		const insertedBlock = createBlock( name, {} );

		insertBlock( insertedBlock, index, currentPage );
	}, [ currentPage, index, insertBlock ] );

	const isReordering = useSelect( ( select ) => select( 'amp/story' ).isReordering(), [] );

	if ( isReordering ) {
		return null;
	}

	const blocks = [
		'amp/amp-story-text',
		'amp/amp-story-cta',
	];

	return (
		blocks.map( ( block ) => {
			if ( ! isBlockAllowedOnPage( block, currentPage ) ) {
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

export default Shortcuts;
