/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Children } from '@wordpress/element';

/**
 * Renders a details element conditionally. If no children are preset, the summary is rendered with no details element.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 * @param {any} props.summary Summary element.
 */
export function ConditionalDetails( { children, summary } ) {
	return children && 0 < Children.toArray( children ).filter( ( child ) => child ).length
		? (
			<details>
				<summary>
					{ summary }
				</summary>

				{ children }

			</details>
		)
		: summary;
}

ConditionalDetails.propTypes = {
	children: PropTypes.any,
	summary: PropTypes.node.isRequired,
};
