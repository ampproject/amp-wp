/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Higher-order component that adds information about selected inner blocks.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	withSelect(
		( select, props ) => {
			const { hasSelectedInnerBlock } = select( 'core/block-editor' );

			return {
				hasSelectedInnerBlock: hasSelectedInnerBlock( props.clientId, true ),
			};
		}
	),
	'withHasSelectedInnerBlock'
);
