/**
 * This is a 1:1 copy of the ChildBlocks component in @wordpress/block-editor.
 *
 * It is included here because the component is not exported to the public by that package.
 */

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { ifCondition, compose } from '@wordpress/compose';
import { BlockIcon } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import BlockTypesList from '../block-types-list';

function ChildBlocks( { rootBlockIcon, rootBlockTitle, items, ...props } ) {
	return (
		<div className="editor-inserter__child-blocks block-editor-inserter__child-blocks">
			{ ( rootBlockIcon || rootBlockTitle ) && (
				<div className="editor-inserter__parent-block-header block-editor-inserter__parent-block-header">
					<BlockIcon icon={ rootBlockIcon } showColors />
					{ rootBlockTitle && <h2>{ rootBlockTitle }</h2> }
				</div>
			) }
			<BlockTypesList items={ items } { ...props } />
		</div>
	);
}

export default compose(
	ifCondition( ( { items } ) => items && items.length > 0 ),
	withSelect( ( select, { rootClientId } ) => {
		const {
			getBlockType,
		} = select( 'core/blocks' );
		const {
			getBlockName,
		} = select( 'core/block-editor' );
		const rootBlockName = getBlockName( rootClientId );
		const rootBlockType = getBlockType( rootBlockName );
		return {
			rootBlockTitle: rootBlockType && rootBlockType.title,
			rootBlockIcon: rootBlockType && rootBlockType.icon,
		};
	} ),
)( ChildBlocks );
