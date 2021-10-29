/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Delay setting a flag value.
 *
 * @param {boolean} flag       Flag value to be delayed.
 * @param {Object}  args       Arguments.
 * @param {number}  args.delay Delay value in ms.
 */
export default function useDelayedFlag( flag, { delay = 500 } = {} ) {
	/**
	 * Delay the `isCompleted` state so that the progress bar stays at 100% for
	 * a brief moment.
	 */
	const [ delayedFlag, setDelayedFlag ] = useState( flag );

	useEffect( () => {
		let cleanup = () => {};

		if ( flag && ! delayedFlag ) {
			cleanup = setTimeout( () => setDelayedFlag( true ), delay );
		} else if ( ! flag && delayedFlag ) {
			setDelayedFlag( false );
		}

		return cleanup;
	}, [ flag, delayedFlag, delay ] );

	return delayedFlag;
}
