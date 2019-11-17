/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useCanvas() {
	return useContext( Context );
}

export default useCanvas;
