/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useLibrary() {
	return useContext( Context );
}

export default useLibrary;
