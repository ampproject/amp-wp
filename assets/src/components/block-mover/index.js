/**
 * This file is mainly copied from the default BlockMover component, there are some small differences.
 * The arrows' labels are changed and are switched. Also, dragging is enabled even if the element is the only block.
 **/

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { withInstanceId, compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { upArrow, downArrow, dragHandle } from './icons';
import { IconDragHandle } from './drag-handle';
import IgnoreNestedEvents from './ignore-nested-events';
import './edit.css';

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
		const { bringForward, sendBackward, isFirst, isLast, isDraggable, onDragStart, clientId, blockElementId, instanceId } = this.props;
		const { isFocused } = this.state;

		// We emulate a disabled state because forcefully applying the `disabled`
		// attribute on the button while it has focus causes the screen to change
		// to an unfocused state (body as active element) without firing blur on,
		// the rendering parent, leaving it unable to react to focus out.
		return (
			<IgnoreNestedEvents childHandledEvents={ [ 'onDragStart', 'onMouseDown' ] }>
				<div className={ classnames( 'amp-story-editor-block-mover editor-block-mover block-editor-block-mover', { 'is-visible': isFocused } ) }>
					<IconButton
						className="editor-block-mover__control block-editor-block-mover__control"
						onClick={ isLast ? null : bringForward }
						icon={ upArrow }
						label={ __( 'Bring Forward', 'amp' ) }
						aria-describedby={ `editor-block-mover__up-description-${ instanceId }` }
						aria-disabled={ isLast }
						onFocus={ this.onFocus }
						onBlur={ this.onBlur }
					/>
					<IconDragHandle
						className="editor-block-mover__control block-editor-block-mover__control"
						icon={ dragHandle }
						clientId={ clientId }
						blockElementId={ blockElementId }
						isVisible={ isDraggable }
						onDragStart={ onDragStart }
					/>
					<IconButton
						className="editor-block-mover__control block-editor-block-mover__control"
						onClick={ isFirst ? null : sendBackward }
						icon={ downArrow }
						label={ __( 'Send Backward', 'amp' ) }
						aria-describedby={ `editor-block-mover__down-description-${ instanceId }` }
						aria-disabled={ isFirst }
						onFocus={ this.onFocus }
						onBlur={ this.onBlur }
					/>
				</div>
			</IgnoreNestedEvents>
		);
	}
}

export default compose(
	withSelect( ( select, { clientId } ) => {
		const { getBlockOrder, getBlockIndex, getTemplateLock, getBlockRootClientId } = select( 'core/block-editor' );
		const rootClientId = getBlockRootClientId( clientId );
		const blockClientIds = getBlockOrder( rootClientId );
		const blockIndex = getBlockIndex( clientId, rootClientId );

		return {
			isFirst: 0 === blockIndex,
			isLast: blockIndex === blockClientIds.length - 1,
			isLocked: getTemplateLock( rootClientId ) === 'all',
			rootClientId,
		};
	} ),
	withDispatch( ( dispatch, { clientId, rootClientId } ) => {
		const { moveBlocksDown, moveBlocksUp } = dispatch( 'core/block-editor' );

		return {
			bringForward: () => moveBlocksDown( clientId, rootClientId ),
			sendBackward: () => moveBlocksUp( clientId, rootClientId ),
		};
	} ),
	withInstanceId,
)( BlockMover );
