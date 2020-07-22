/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Hook providing the current window width as state.
 */
export function useWindowWidth() {
	const [ width, setWidth ] = useState( window.innerWidth );

	useEffect( () => {
		const resizeCallback = () => {
			setWidth( window.innerWidth );
		};

		window.addEventListener( 'resize', resizeCallback );

		return () => {
			window.removeEventListener( 'resize', resizeCallback );
		};
	}, [] );

	return width;
}
