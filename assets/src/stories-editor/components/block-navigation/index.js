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

function BlockNavigationList( { blocks,	selectedBlockClientId, selectBlock } ) {
	return (
		/*
		 * Disable reason: The `list` ARIA role is redundant but
		 * Safari+VoiceOver won't announce the list otherwise.
		 */
		/* eslint-disable jsx-a11y/no-redundant-roles */
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
		/* eslint-enable jsx-a11y/no-redundant-roles */
	);
}

BlockNavigationList.propTypes = {
	blocks: PropTypes.arrayOf( PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ) ).isRequired,
	selectedBlockClientId: PropTypes.string,
	selectBlock: PropTypes.func.isRequired,
};

function BlockNavigation( { blocks, selectBlock, selectedBlockClientId } ) {
	const hasBlocks = blocks.length > 0;

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

		const blocks = getCurrentPage() ? getBlocksByClientId( getBlockOrder( getCurrentPage() ) ) : [];

		return {
			blocks: blocks.filter( ( { name } ) => ALLOWED_MOVABLE_BLOCKS.includes( name ) ).reverse(),
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
