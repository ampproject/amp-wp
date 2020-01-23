/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useUnits() {
	return useContext( Context );
}

export default useUnits;
