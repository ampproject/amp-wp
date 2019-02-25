/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

export default createHigherOrderComponent(
	withSelect(
		( select, props ) => {
			const { getBlockAttributes } = select( 'core/editor' );

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
	'withParentBlock'
);
