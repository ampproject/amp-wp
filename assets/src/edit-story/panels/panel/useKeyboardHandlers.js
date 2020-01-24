/**
 * WordPress dependencies
 */
import {
	useCallback,
	useLayoutEffect,
} from '@wordpress/element';

const KEY_UP = 38;
const KEY_DOWN = 40;
const DELTA_CHANGE = 20; // change in pixels when pressing arrow keys

function useKeyboardHandlers( handle, handleHeightChange ) {
	// Handle up/down keypresses to move separator.
	// TODO Should be rewritten to use MouseTrap when added to .
	const handleKeyPress = useCallback( ( evt ) => {
		if ( [ KEY_UP, KEY_DOWN ].includes( evt.keyCode ) ) {
			const direction = evt.keyCode === KEY_UP ? 1 : -1;
			handleHeightChange( direction * DELTA_CHANGE );
			evt.stopPropagation();
			evt.preventDefault();
		}
	}, [ handleHeightChange ] );

	// On initial render assign keyboard listener to handle up/down arrow presses.
	useLayoutEffect( () => {
		const element = handle.current;
		element.addEventListener( 'keydown', handleKeyPress );

		return () => {
			if ( element ) {
				element.removeEventListener( 'keydown', handleKeyPress );
			}
		};
	}, [ handleKeyPress, handle ] );
}

export default useKeyboardHandlers;
