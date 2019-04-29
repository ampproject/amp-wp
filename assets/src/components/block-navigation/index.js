/**
 * WordPress dependencies
 */
import { withSelect, withDispatch } from '@wordpress/data';
import { Button, NavigableMenu } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { BlockPreviewLabel } from '../';
import './edit.css';

/**
 * Internal dependencies
 */
import { ALLOWED_MOVABLE_BLOCKS } from '../../constants';

function BlockNavigationList( { blocks,	selectedBlockClientId, selectBlock } ) {
	return (
		/*
		 * Disable reason: The `list` ARIA role is redundant but
		 * Safari+VoiceOver won't announce the list otherwise.
		 */
		/* eslint-disable jsx-a11y/no-redundant-roles */
		<ul key="navigation-list" className="editor-block-navigation__list block-editor-block-navigation__list" role="list">
			{ blocks.map( ( block ) => {
				const isSelected = block.clientId === selectedBlockClientId;

				let className = 'components-button editor-block-navigation__item-button block-editor-block-navigation__item-button';
				if ( isSelected ) {
					className += ' is-selected';
				}

				return (
					<li key={ block.clientId }>
						<div className="editor-block-navigation__item block-editor-block-navigation__item">
							<Button
								className={ className }
								onClick={ () => selectBlock( block.clientId ) }
							>
								<BlockPreviewLabel
									block={ block }
									accessibilityText={ isSelected && __( '(selected block)', 'amp' ) }
								/>
							</Button>
						</div>
					</li>
				);
			} ) }
		</ul>
		/* eslint-enable jsx-a11y/no-redundant-roles */
	);
}

function BlockNavigation( { blocks, selectBlock, selectedBlockClientId, isReordering } ) {
	const hasBlocks = blocks.length > 0;

	if ( isReordering ) {
		return null;
	}

	return (
		<NavigableMenu
			role="presentation"
			className="editor-block-navigation__container block-editor-block-navigation__container"
		>
			<p className="editor-block-navigation__label">{ __( 'Block Navigation', 'amp' ) }</p>
			{ hasBlocks && (
				<BlockNavigationList
					blocks={ blocks }
					selectedBlockClientId={ selectedBlockClientId }
					selectBlock={ selectBlock }
				/>
			) }
			{ ! hasBlocks && (
				<p className="editor-block-navigation__paragraph">
					{ __( 'No blocks created yet.', 'amp' ) }
				</p>
			) }
		</NavigableMenu>
	);
}

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
	} )
)( BlockNavigation );
