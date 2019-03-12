/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Higher-order component that adds information about the block's parent.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	withSelect(
		( select, props ) => {
			const { getBlockRootClientId, getBlock } = select( 'core/editor' );

			return {
				parentBlock: getBlock( getBlockRootClientId( props.clientId ) ),
			};
		}
	),
	'withParentBlock'
);
