/**
 * WordPress dependencies
 */
import { useEffect, useState, useRef } from '@wordpress/element';
/**
 * External dependencies
 */
import { debounce } from 'lodash';

/**
 * Hook providing the current window width as state.
 *
 * @param element
 * @param rootElement
 */
export function useScroll( rootElement = global || window ) {
	const [ scrolling, setScrolling ] = useState( false );

	const { scrollLeft, scrollTop } = rootElement;

	const mounted = useRef( false );

	useEffect( () => {
		mounted.current = true;

		return () => {
			mounted.current = false;
		};
	}, [] );

	useEffect( () => {
		if ( scrolling ) {
			return () => null;
		}

		const scrollingCallback = debounce( () => {
			setScrolling( true );

			window.setTimeout( () => {
				if ( mounted ) {
					setScrolling( false );
				}
			}, 25 );
		}, 100 );

		rootElement.addEventListener( 'scroll', scrollingCallback );

		return () => {
			rootElement.removeEventListener( 'scroll', scrollingCallback );
		};
	}, [ rootElement, scrolling ] );

	return { scrollLeft, scrollTop, scrolling };
}
