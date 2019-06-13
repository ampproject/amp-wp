/**
 * This file is mainly copied from the default BlockMover component, there are some small differences.
 * The arrows' labels are changed and are switched. Also, dragging is enabled even if the element is the only block.
 *
 * In addition, this copy also contains prop types.
 **/

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

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
			<IgnoreNestedEvents childHandledEvents={ [ 'onDragStart', 'onMouseDown' ] } className="block-mover">
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

BlockMover.propTypes = {
	isDraggable: PropTypes.bool,
	isMovable: PropTypes.bool,
	onDragStart: PropTypes.func,
	clientId: PropTypes.string,
	blockElementId: PropTypes.string,
	children: PropTypes.any.isRequired,
};

export default BlockMover;
