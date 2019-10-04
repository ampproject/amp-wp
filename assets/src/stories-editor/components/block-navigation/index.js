/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { DropZoneProvider, NavigableMenu } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { isMovableBlock } from '../../helpers';
import BlockNavigationItem from './item';
import './edit.css';

function BlockNavigationList( { blocks, selectedBlockClientId, unMovableBlock } ) {
	const { selectBlock } = useDispatch( 'core/block-editor' );

	return (
		/*
		 * Disable reason: The `list` ARIA role is redundant but
		 * Safari+VoiceOver won't announce the list otherwise.
		 */
		/* eslint-disable jsx-a11y/no-redundant-roles */
		<>
			{ blocks.length > 0 && (
				<DropZoneProvider>
					<ul className="editor-block-navigation__list block-editor-block-navigation__list" role="list">
						{ blocks.map( ( block ) => {
							const isSelected = block.clientId === selectedBlockClientId;

							return (
								<li key={ block.clientId }>
									<BlockNavigationItem
										block={ block }
										isSelected={ isSelected }
										onClick={ () => selectBlock( block.clientId ) }
									/>
								</li>
							);
						} ) }
					</ul>
				</DropZoneProvider>
			) }
			{ /* Add CTA/Attachment block separately to exclude it from DropZone. */ }
			{ unMovableBlock && (
				<ul className="editor-block-navigation__list block-editor-block-navigation__list editor-block-navigation__list__static" role="list">
					<li key={ unMovableBlock.clientId }>
						<BlockNavigationItem
							block={ unMovableBlock }
							isSelected={ unMovableBlock.clientId === selectedBlockClientId }
							onClick={ () => selectBlock( unMovableBlock.clientId ) }
						/>
					</li>
				</ul>
			) }
		</>
		/* eslint-enable jsx-a11y/no-redundant-roles */
	);
}

BlockNavigationList.propTypes = {
	blocks: PropTypes.arrayOf( PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ) ).isRequired,
	selectedBlockClientId: PropTypes.string,
	unMovableBlock: PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ),
};

function BlockNavigation() {
	const {
		blocks,
		unMovableBlock,
		selectedBlockClientId,
	} = useSelect( ( select ) => {
		const { getCurrentPage } = select( 'amp/story' );
		const { getBlockOrder, getBlocksByClientId, getSelectedBlockClientId } = select( 'core/block-editor' );

		const allBlocks = getCurrentPage() ? getBlocksByClientId( getBlockOrder( getCurrentPage() ) ) : [];
		const movableBlocks = allBlocks.filter( ( { name } ) => isMovableBlock( name ) ).reverse();

		// Let's get the CTA/Attachment block to handle it separately.
		const _unMovableBlock = allBlocks.find( ( { name } ) => ! isMovableBlock( name ) );

		return {
			blocks: movableBlocks,
			unMovableBlock: _unMovableBlock,
			selectedBlockClientId: getSelectedBlockClientId(),
		};
	}, [] );

	const isReordering = useSelect( ( select ) => select( 'amp/story' ).isReordering(), [] );

	if ( isReordering ) {
		return null;
	}

	const hasBlocks = blocks.length > 0 || unMovableBlock;

	return (
		<NavigableMenu
			role="presentation"
			className="block-editor-block-navigation__container"
		>
			<p className="block-editor-block-navigation__label">
				{ __( 'Elements', 'amp' ) }
			</p>
			{ hasBlocks && (
				<BlockNavigationList
					blocks={ blocks }
					selectedBlockClientId={ selectedBlockClientId }
					unMovableBlock={ unMovableBlock }
				/>
			) }
			{ ! hasBlocks && (
				<p className="block-editor-block-navigation__paragraph">
					{ __( 'No elements added to this page yet.', 'amp' ) }
				</p>
			) }
		</NavigableMenu>
	);
}

export default BlockNavigation;

