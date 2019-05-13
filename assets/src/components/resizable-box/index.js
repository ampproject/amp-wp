/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { ResizableBox } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';
import { getDelta } from '../../stories-editor/helpers';

let lastSeenX = 0;
let lastSeenY = 0;

export default ( props ) => {
	const { isSelected, angle, width, height, onResizeStart, onResizeStop, children, ...otherProps } = props;

	return (
		<ResizableBox
			{ ...otherProps }
			className={ classnames(
				'amp-story-resize-container',
				{ 'is-selected': isSelected }
			) }
			size={ {
				height,
				width,
			} }
			// Adding only right and bottom since otherwise it needs to change the top and left position, too.
			enable={ {
				top: false,
				right: true,
				bottom: true,
				left: false,
			} }
			onResizeStop={ ( event, direction ) => {
				const { deltaW, deltaH } = getDelta( event, angle, lastSeenX, lastSeenY, direction );
				onResizeStop( {
					width: parseInt( width + deltaW, 10 ),
					height: parseInt( height + deltaH, 10 ),
				} );
			} }
			onResizeStart={ ( event ) => {
				lastSeenX = event.clientX;
				lastSeenY = event.clientY;
				onResizeStart();
			} }
			onResize={ ( event, direction, element ) => {
				const { deltaW, deltaH } = getDelta( event, angle, lastSeenX, lastSeenY, direction );
				element.style.width = width + deltaW + 'px';
				element.style.height = height + deltaH + 'px';
			} }
		>
			{ children }
		</ResizableBox>
	);
};
