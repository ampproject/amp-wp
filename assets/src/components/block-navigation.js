/**
 * WordPress dependencies
 */
import { withSelect, withDispatch } from '@wordpress/data';
import { Button, NavigableMenu } from '@wordpress/components';
import { getBlockType } from '@wordpress/blocks';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { BlockIcon } from '@wordpress/editor';

function BlockNavigationList( { blocks,	selectedBlockClientId, selectBlock } ) {
	return (
		/*
		 * Disable reason: The `list` ARIA role is redundant but
		 * Safari+VoiceOver won't announce the list otherwise.
		 */
		/* eslint-disable jsx-a11y/no-redundant-roles */
		<ul key="navigation-list" className="editor-block-navigation__list" role="list">
			{ blocks.map( ( block ) => {
				const blockType = getBlockType( block.name );
				const isSelected = block.clientId === selectedBlockClientId;

				let className = 'editor-block-navigation__item-button';
				if ( isSelected ) {
					className += ' is-selected';
				}

				return (
					<li key={ block.clientId }>
						<div className="editor-block-navigation__item">
							<Button
								className={ className }
								onClick={ () => selectBlock( block.clientId ) }
							>
								<BlockIcon icon={ blockType.icon } showColors />
								{ blockType.title }
								{ isSelected && <span className="screen-reader-text">{ __( '(selected block)', 'amp' ) }</span> }
							</Button>
						</div>
					</li>
				);
			} ) }
		</ul>
		/* eslint-enable jsx-a11y/no-redundant-roles */
	);
}

function BlockNavigation( { elements, selectBlock, selectedBlockClientId } ) {
	const hasElements = elements.length > 0;

	return (
		<NavigableMenu
			role="presentation"
			className="editor-block-navigation__container"
		>
			<p className="editor-block-navigation__label">{ __( 'Block Navigation', 'amp' ) }</p>
			{ hasElements && (
				<BlockNavigationList
					blocks={ elements }
					selectedBlockClientId={ selectedBlockClientId }
					selectBlock={ selectBlock }
				/>
			) }
			{ ! hasElements && (
				<p className="editor-block-navigation__paragraph">
					{ __( 'No blocks created yet.', 'amp' ) }
				</p>
			) }
		</NavigableMenu>
	);
}

export default compose(
	withSelect( ( select ) => {
		const { getCurrentPage } = select( 'amp/story' );
		const { getBlockOrder, getBlocksByClientId, getSelectedBlockClientId } = select( 'core/editor' );

		return {
			elements: getBlocksByClientId( getBlockOrder( getCurrentPage() ) ),
			selectedBlockClientId: getSelectedBlockClientId(),
		};
	} ),
	withDispatch( ( dispatch, { onSelect = () => undefined } ) => {
		return {
			selectBlock( clientId ) {
				dispatch( 'core/editor' ).selectBlock( clientId );
				onSelect( clientId );
			},
		};
	} )
)( BlockNavigation );
