/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Detects clicks on a referenced element.
 *
 * @param {Object} ref Reference.
 * @param {Function} onDetected Action when detected.
 * @param {boolean} clickRef Whether to trigger click of the referenced element when click detected.
 */
const useElementClickDetector = ( ref, onDetected = null, clickRef = false ) => {
	/**
	 * Click handling.
	 *
	 * @param {Object} event Click event.
	 */
	function handleClick( event ) {
		if ( ref.current && ref.current.contains( event.target ) ) {
			if ( onDetected ) {
				onDetected();
			}
			if ( clickRef ) {
				ref.current.click();
			}
		}
	}

	useEffect( () => {
		if ( ref.current ) {
			document.addEventListener( 'mousedown', handleClick );
		}
		return () => {
			// Unbind when cleaning up.
			document.removeEventListener( 'mousedown', handleClick );
		};
	} );
};

export default useElementClickDetector;
