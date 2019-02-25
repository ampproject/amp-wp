/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

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
