/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * Delay setting a flag value.
 *
 * @param {boolean} flag       Flag value to be delayed.
 * @param {Object}  args       Arguments.
 * @param {number}  args.delay Delay value in ms.
 */
export default function useDelayedFlag(flag, { delay = 500 } = {}) {
	/**
	 * Delay the `isCompleted` state so that the progress bar stays at 100% for
	 * a brief moment.
	 */
	const [delayedFlag, setDelayedFlag] = useState(flag);

	/**
	 * This component sets state inside async functions. Use this ref to prevent
	 * state updates after unmount.
	 */
	const hasUnmounted = useRef(false);
	useEffect(
		() => () => {
			hasUnmounted.current = true;
		},
		[]
	);

	useEffect(() => {
		let cleanup = () => {};

		if (flag && !delayedFlag) {
			cleanup = setTimeout(() => {
				if (!hasUnmounted.current) {
					setDelayedFlag(true);
				}
			}, delay);
		} else if (!flag && delayedFlag) {
			setDelayedFlag(false);
		}

		return () => {
			clearTimeout(cleanup);
		};
	}, [flag, delayedFlag, delay]);

	return delayedFlag;
}
