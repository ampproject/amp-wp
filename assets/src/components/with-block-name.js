/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

export default createHigherOrderComponent(
	withSelect(
		( select, props ) => {
			const { getBlockName } = select( 'core/editor' );

			return {
				blockName: getBlockName( props.clientId ),
			};
		}
	),
	'withBlockName'
);
