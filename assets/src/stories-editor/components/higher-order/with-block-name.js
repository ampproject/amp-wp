/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Higher-order component that passes the block's name as props.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	withSelect(
		( select, props ) => {
			const { getBlockName } = select( 'core/block-editor' );

			return {
				blockName: getBlockName( props.clientId ),
			};
		}
	),
	'withBlockName'
);
