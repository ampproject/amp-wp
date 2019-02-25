/**
 * WordPress dependencies
 */
import { withSelect, withDispatch } from '@wordpress/data';
import { Button, NavigableMenu } from '@wordpress/components';
import { getBlockType } from '@wordpress/blocks';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { BlockIcon } from '@wordpress/editor';

function BlockNavigationList( {
	blocks,
	selectedBlockClientId,
	selectBlock,
	showNestedBlocks,
} ) {
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
						{ showNestedBlocks && !! block.innerBlocks && !! block.innerBlocks.length && (
							<BlockNavigationList
								blocks={ block.innerBlocks }
								selectedBlockClientId={ selectedBlockClientId }
								selectBlock={ selectBlock }
								showNestedBlocks
							/>
						) }
					</li>
				);
			} ) }
		</ul>
		/* eslint-enable jsx-a11y/no-redundant-roles */
	);
}

function BlockNavigation( { rootBlock, rootBlocks, selectedBlockClientId, selectBlock } ) {
	const hasHierarchy = (
		rootBlock && (
			rootBlock.clientId !== selectedBlockClientId ||
			( rootBlock.innerBlocks && rootBlock.innerBlocks.length !== 0 )
		)
	);

	return (
		<NavigableMenu
			role="presentation"
			className="editor-block-navigation__container"
		>
			<p className="editor-block-navigation__label">{ __( 'Block Navigation', 'amp' ) }</p>
			{ hasHierarchy && (
				<BlockNavigationList
					blocks={ [ rootBlock ] }
					selectedBlockClientId={ selectedBlockClientId }
					selectBlock={ selectBlock }
					showNestedBlocks
				/>
			) }
			{ ! hasHierarchy && (
				<BlockNavigationList
					blocks={ rootBlocks }
					selectedBlockClientId={ selectedBlockClientId }
					selectBlock={ selectBlock }
				/>
			) }
			{ ( ! rootBlocks || rootBlocks.length === 0 ) && (
				// If there are no blocks in this document, don't render a list of blocks.
				// Instead: inform the user no blocks exist yet.
				<p className="editor-block-navigation__paragraph">
					{ __( 'No blocks created yet.', 'amp' ) }
				</p>
			) }
		</NavigableMenu>
	);
}

export default compose(
	withSelect( ( select ) => {
		const {
			getSelectedBlockClientId,
			getBlockHierarchyRootClientId,
			getBlock,
			getBlocks,
		} = select( 'core/editor' );

		const selectedBlockClientId = getSelectedBlockClientId();

		return {
			rootBlocks: getBlocks().filter( ( block ) => block.name === 'amp/amp-story-page' ),
			rootBlock: selectedBlockClientId ? getBlock( getBlockHierarchyRootClientId( selectedBlockClientId ) ) : null,
			selectedBlockClientId,
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
