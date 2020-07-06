/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * @todo WIP: Updated design needed.
 */
export function Loading() {
	return (
		<div className="amp-spinner-container">
			<Spinner />
		</div>
	);
}
