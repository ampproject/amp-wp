
/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useHistory() {
	return useContext( Context );
}

export default useHistory;
