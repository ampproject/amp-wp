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

/**
 * Internal dependencies
 */
import { BlockDragArea } from './block-drag-area';
import IgnoreNestedEvents from './ignore-nested-events';
import './edit.css';

export class BlockMover extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {
		const { children, isDraggable, isMovable, onDragStart, clientId, blockElementId } = this.props;

		if ( ! isMovable || ! isDraggable ) {
			return children;
		}

		// We emulate a disabled state because forcefully applying the `disabled`
		// attribute on the button while it has focus causes the screen to change
		// to an unfocused state (body as active element) without firing blur on,
		// the rendering parent, leaving it unable to react to focus out.
		return (
			<IgnoreNestedEvents childHandledEvents={ [ 'onDragStart', 'onMouseDown' ] }>
				<div>
					<BlockDragArea
						children={ children }
						clientId={ clientId }
						blockElementId={ blockElementId }
						onDragStart={ onDragStart }
					/>
				</div>
			</IgnoreNestedEvents>
		);
	}
}

export default BlockMover;
