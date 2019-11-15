/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useConfig() {
	return useContext( Context );
}

export default useConfig;
