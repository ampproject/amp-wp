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

export default ( props ) => {
	const { isSelected, width, height, onResizeStart, onResizeStop, children, ...otherProps } = props;

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
			onResizeStop={ ( event, direction, elt, delta ) => {
				onResizeStop( {
					width: parseInt( width + delta.width, 10 ),
					height: parseInt( height + delta.height, 10 ),
				} );
			} }
			onResizeStart={ onResizeStart }
		>
			{ children }
		</ResizableBox>
	);
};
