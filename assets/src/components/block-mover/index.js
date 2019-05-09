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
import { Component } from '@wordpress/element';
import { withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { dragHandle } from './icons';
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
		const { isDraggable, onDragStart, onDragEnd, clientId, blockElementId } = this.props;
		const { isFocused } = this.state;

		// We emulate a disabled state because forcefully applying the `disabled`
		// attribute on the button while it has focus causes the screen to change
		// to an unfocused state (body as active element) without firing blur on,
		// the rendering parent, leaving it unable to react to focus out.
		return (
			<IgnoreNestedEvents childHandledEvents={ [ 'onDragStart', 'onMouseDown' ] }>
				<div className={ classnames( 'amp-story-editor-block-mover editor-block-mover block-editor-block-mover', { 'is-visible': isFocused } ) }>
					<IconDragHandle
						className="editor-block-mover__control block-editor-block-mover__control"
						icon={ dragHandle }
						clientId={ clientId }
						blockElementId={ blockElementId }
						isVisible={ isDraggable }
						onDragStart={ onDragStart }
						onDragEnd={ onDragEnd }
					/>
				</div>
			</IgnoreNestedEvents>
		);
	}
}

export default withDispatch( ( dispatch ) => {
	const { clearSelectedBlock } = dispatch( 'core/block-editor' );
	return {
		onDragEnd: clearSelectedBlock,
	};
} )( BlockMover );
