/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Higher-order component that adds information about the reordering status.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	withSelect(
		( select ) => {
			const { isReordering } = select( 'amp/story' );

			return {
				isReordering: isReordering(),
			};
		}
	),
	'withIsReordering'
);
