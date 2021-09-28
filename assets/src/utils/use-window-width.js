/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { DEFAULT_MOBILE_BREAKPOINT } from '../common/constants';

/**
 * Hook providing the current window width as state.
 *
 * @param {Object} args Hook arguments.
 * @param {number} args.mobileBreakpoint The mobile breakpoint in pixels.
 */
export function useWindowWidth( args = {} ) {
	args = {
		...args,
		mobileBreakpoint: DEFAULT_MOBILE_BREAKPOINT,
	};

	const { mobileBreakpoint } = args;

	const [ width, setWidth ] = useState( window.innerWidth );

	useEffect( () => {
		const resizeCallback = () => {
			setWidth( window.innerWidth );
		};

		global.addEventListener( 'resize', resizeCallback, { passive: true } );

		return () => {
			global.removeEventListener( 'resize', resizeCallback );
		};
	}, [] );

	return { windowWidth: width, isMobile: width < mobileBreakpoint };
}
