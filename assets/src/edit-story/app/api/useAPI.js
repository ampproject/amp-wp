
/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useAPI() {
	return useContext( Context );
}

export default useAPI;
