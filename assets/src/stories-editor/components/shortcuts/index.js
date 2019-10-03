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
import { IconButton } from '@wordpress/components';
import { compose, ifCondition } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { useIsBlockAllowedOnPage } from '../../helpers';

const Shortcuts = ( { insertBlock, currentPage, showInserter } ) => {
	const blocks = [
		'amp/amp-story-text',
		'amp/amp-story-cta',
	];

	const isBlockAllowedOnPage = useIsBlockAllowedOnPage();

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
					onClick={ () => insertBlock( block ) }
					label={ blockType.title }
					labelPosition="bottom"
					disabled={ ! showInserter }
				/>
			);
		} )
	);
};

Shortcuts.propTypes = {
	insertBlock: PropTypes.func.isRequired,
	currentPage: PropTypes.string.isRequired,
	showInserter: PropTypes.bool.isRequired,
};

const applyWithSelect = withSelect( ( select ) => {
	const { getCurrentPage } = select( 'amp/story' );
	const { isReordering } = select( 'amp/story' );

	const currentPage = getCurrentPage();

	return {
		isReordering: isReordering(),
		currentPage,
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
)( Shortcuts );
