/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function CanvasProvider( { children } ) {
	const [ isEditMode, setIsEditMode ] = useState( false );
	// It's a bit weird to directly set a state to be a function (as setFoo calls
	// any function given to unwrap the inner value, which can then be a function),
	// so we use a wrapper object in stead of double-functioning.
	// We create helper functions below to set it directly.
	const [ backgroundClick, setBackgroundClick ] = useState( { handler: null } );

	const setBackgroundClickHandler = useCallback(
		( handler ) => setBackgroundClick( { handler: typeof handler === 'function' ? handler : null } ),
		[ setBackgroundClick ],
	);

	const clearBackgroundClickHandler = useCallback(
		() => setBackgroundClick( { handler: null } ),
		[ setBackgroundClick ],
	);

	const state = {
		state: {
			isEditMode,
			backgroundClickHandler: backgroundClick.handler,
		},
		actions: {
			setBackgroundClickHandler,
			clearBackgroundClickHandler,
			setIsEditMode,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

CanvasProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default CanvasProvider;
