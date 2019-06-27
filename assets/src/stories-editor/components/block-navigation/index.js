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
import BlockNavigationItem from './item';
import { ALLOWED_MOVABLE_BLOCKS } from '../../constants';
import './edit.css';

function BlockNavigationList( { blocks,	selectedBlockClientId, selectBlock, callToActionBlock } ) {
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
			{ /* Add CTA block separately to exclude it from DropZone. */ }
			{ callToActionBlock && (
				<ul className="editor-block-navigation__list block-editor-block-navigation__list editor-block-navigation__list__static" role="list">
					<li key={ callToActionBlock.clientId }>
						<BlockNavigationItem
							block={ callToActionBlock }
							isSelected={ callToActionBlock.clientId === selectedBlockClientId }
							onClick={ () => selectBlock( callToActionBlock.clientId ) }
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
	callToActionBlock: PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ),
};

function BlockNavigation( { callToActionBlock, blocks, selectBlock, selectedBlockClientId } ) {
	const hasBlocks = blocks.length > 0 || callToActionBlock;

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
					callToActionBlock={ callToActionBlock }
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
	callToActionBlock: PropTypes.shape( {
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
		// Let's get the CTA block to handle it separately.
		const callToActionBlock = blocks.find( ( { name } ) => name === 'amp/amp-story-cta' );
		blocks = blocks.filter( ( { name } ) => ALLOWED_MOVABLE_BLOCKS.includes( name ) ).reverse();
		return {
			blocks,
			callToActionBlock,
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
