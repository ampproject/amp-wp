/**
 * This file is mainly copied from the default BlockMover component, there are some small differences.
 * The arrows' labels are changed and are switched. Also, dragging is enabled even if the element is the only block.
 * See the diff here: https://gist.github.com/miina/1e9835ed93d752987685bf133d123d4d/revisions#diff-f7a1098c9549e3334c67c9ac7c146e9d
 */

/**
 * External dependencies
 */
import { first, partial, castArray } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton } from '@wordpress/components';
import { getBlockType } from '@wordpress/blocks';
import { Component } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { withInstanceId, compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { upArrow, downArrow, dragHandle } from './icons';
import { IconDragHandle } from './drag-handle';

export class BlockMover extends Component {
	constructor() {
		super( ...arguments );
		this.state = {
			isFocused: false,
		};
		this.onFocus = this.onFocus.bind( this );
		this.onBlur = this.onBlur.bind( this );
	}

	onFocus() {
		this.setState( {
			isFocused: true,
		} );
	}

	onBlur() {
		this.setState( {
			isFocused: false,
		} );
	}

	render() {
		const { onMoveUp, onMoveDown, isFirst, isLast, isDraggable, onDragStart, onDragEnd, clientIds, blockElementId, blockType, firstIndex, instanceId } = this.props;
		const { isFocused } = this.state;
		const blocksCount = castArray( clientIds ).length;

		// We emulate a disabled state because forcefully applying the `disabled`
		// attribute on the button while it has focus causes the screen to change
		// to an unfocused state (body as active element) without firing blur on,
		// the rendering parent, leaving it unable to react to focus out.
		return (
			<div className={ classnames( 'amp-story-editor-block-mover', { 'is-visible': isFocused } ) }>

				<IconButton
					className="editor-block-mover__control"
					onClick={ isLast ? null : onMoveDown }
					icon={ downArrow }
					label={ __( 'Bring forward', 'amp' ) }
					aria-describedby={ `editor-block-mover__down-description-${ instanceId }` }
					aria-disabled={ isLast }
					onFocus={ this.onFocus }
					onBlur={ this.onBlur }
				/>
				<IconDragHandle
					className="editor-block-mover__control"
					icon={ dragHandle }
					clientId={ clientIds }
					blockElementId={ blockElementId }
					isVisible={ isDraggable }
					onDragStart={ onDragStart }
					onDragEnd={ onDragEnd }
				/>
				<IconButton
					className="editor-block-mover__control"
					onClick={ isFirst ? null : onMoveUp }
					icon={ upArrow }
					label={ __( 'Move to back', 'amp' ) }
					aria-describedby={ `editor-block-mover__up-description-${ instanceId }` }
					aria-disabled={ isFirst }
					onFocus={ this.onFocus }
					onBlur={ this.onBlur }
				/>
			</div>
		);
	}
}

export default compose(
	withSelect( ( select, { clientIds } ) => {
		const { getBlock, getBlockIndex, getTemplateLock, getBlockRootClientId } = select( 'core/block-editor' );
		const firstClientId = first( castArray( clientIds ) );
		const block = getBlock( firstClientId );
		const rootClientId = getBlockRootClientId( first( castArray( clientIds ) ) );

		return {
			firstIndex: getBlockIndex( firstClientId, rootClientId ),
			blockType: block ? getBlockType( block.name ) : null,
			isLocked: getTemplateLock( rootClientId ) === 'all',
			rootClientId,
		};
	} ),
	withDispatch( ( dispatch, { clientIds, rootClientId } ) => {
		const { moveBlocksDown, moveBlocksUp } = dispatch( 'core/block-editor' );
		return {
			onMoveDown: partial( moveBlocksDown, clientIds, rootClientId ),
			onMoveUp: partial( moveBlocksUp, clientIds, rootClientId ),
		};
	} ),
	withInstanceId,
)( BlockMover );
