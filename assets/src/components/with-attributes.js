/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Higher-order component that passes the block's attributes as props.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	withSelect(
		( select, props ) => {
			const { getBlockAttributes } = select( 'core/block-editor' );

			let attributes;

			if ( props.block && props.block.attributes ) {
				attributes = props.block.attributes;
			} else if ( getBlockAttributes ) {
				attributes = getBlockAttributes( props.clientId );
			}

			return {
				attributes,
			};
		}
	),
	'withAttributes'
);
