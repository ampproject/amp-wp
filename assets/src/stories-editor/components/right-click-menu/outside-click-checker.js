/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Detects clicks outside of the referenced element.
 *
 * @param {Object} ref Reference.
 * @param {Function} onDetected Action when detected.
 */
const useOutsideClickChecker = ( ref, onDetected ) => {
	/**
	 * Close the Popover if outside click was detected.
	 *
	 * @param {Object} event Click event.
	 */
	function handleClickOutside( event ) {
		if ( ref.current && ! ref.current.contains( event.target ) ) {
			onDetected();
		}
	}

	useEffect( () => {
		// Handle click outside only if the the menu has been added.
		if ( ref.current && ref.current.innerHTML ) {
			document.addEventListener( 'mousedown', handleClickOutside );
		}
		return () => {
			// Unbind when cleaning up.
			document.removeEventListener( 'mousedown', handleClickOutside );
		};
	} );
};

export default useOutsideClickChecker;
