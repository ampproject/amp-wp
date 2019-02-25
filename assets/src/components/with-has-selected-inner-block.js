/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

export default createHigherOrderComponent(
	withSelect(
		( select, props ) => {
			const { hasSelectedInnerBlock } = select( 'core/editor' );

			return {
				hasSelectedInnerBlock: hasSelectedInnerBlock( props.clientId, true ),
			};
		}
	),
	'withHasSelectedInnerBlock'
);
