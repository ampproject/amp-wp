/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { withSelect, withDispatch } from '@wordpress/data';
import { DropZoneProvider, NavigableMenu } from '@wordpress/components';
import { compose, ifCondition } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { isMovableBlock } from '../../helpers';
import BlockNavigationItem from './item';
import './edit.css';

function BlockNavigationList( { blocks, selectedBlockClientId, selectBlock, unMovableBlock } ) {
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
	selectBlock: PropTypes.func.isRequired,
	unMovableBlock: PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ),
};

function BlockNavigation( { unMovableBlock, blocks, selectBlock, selectedBlockClientId } ) {
	const hasBlocks = blocks.length > 0 || unMovableBlock;

	return (
		<NavigableMenu
			role="presentation"
			className="block-editor-block-navigation__container"
		>
			<p className="block-editor-block-navigation__label">{ __( 'Elements', 'amp' ) }</p>
			{ hasBlocks && (
				<BlockNavigationList
					blocks={ blocks }
					selectedBlockClientId={ selectedBlockClientId }
					selectBlock={ selectBlock }
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

BlockNavigation.propTypes = {
	unMovableBlock: PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ),
	blocks: PropTypes.arrayOf( PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ) ).isRequired,
	selectedBlockClientId: PropTypes.string,
	selectBlock: PropTypes.func.isRequired,
	isReordering: PropTypes.bool.isRequired,
};

export default compose(
	withSelect( ( select ) => {
		const { getCurrentPage, isReordering } = select( 'amp/story' );
		const { getBlockOrder, getBlocksByClientId, getSelectedBlockClientId } = select( 'core/block-editor' );

		let blocks = getCurrentPage() ? getBlocksByClientId( getBlockOrder( getCurrentPage() ) ) : [];
		// Let's get the CTA/Attachment block to handle it separately.
		const unMovableBlock = blocks.find( ( { name } ) => ! isMovableBlock( name ) );
		blocks = blocks.filter( ( { name } ) => isMovableBlock( name ) ).reverse();
		return {
			blocks,
			unMovableBlock,
			selectedBlockClientId: getSelectedBlockClientId(),
			isReordering: isReordering(),
		};
	} ),
	withDispatch( ( dispatch, { onSelect = () => undefined } ) => {
		return {
			selectBlock( clientId ) {
				dispatch( 'core/block-editor' ).selectBlock( clientId );
				onSelect( clientId );
			},
		};
	} ),
	ifCondition( ( { isReordering } ) => ! isReordering ),
)( BlockNavigation );
