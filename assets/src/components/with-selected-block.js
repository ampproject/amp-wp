/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

export default createHigherOrderComponent(
	withSelect(
		( select ) => {
			const { getSelectedBlock } = select( 'core/editor' );

			return {
				selectedBlock: getSelectedBlock(),
			};
		}
	),
	'withSelectedBlock'
);
