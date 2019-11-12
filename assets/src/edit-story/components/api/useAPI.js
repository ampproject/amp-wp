
/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useAPI() {
	const { methods } = useContext( Context );
	return methods;
}

export default useAPI;
