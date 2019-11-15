
/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function useAPI() {
	const { actions } = useContext( Context );
	return actions;
}

export default useAPI;
