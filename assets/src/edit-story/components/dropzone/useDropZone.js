/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useDropZone() {
	return useContext( Context );
}

export default useDropZone;
