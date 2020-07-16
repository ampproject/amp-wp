/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useState } from '@wordpress/element';

export const ErrorContext = createContext();

/**
 * Error context provider.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 */
export function ErrorContextProvider( { children } ) {
	const [ error, setError ] = useState( error );

	return (
		<ErrorContext.Provider value={ { error, setError } }>
			{ children }
		</ErrorContext.Provider>
	);
}

ErrorContextProvider.propTypes = {
	children: PropTypes.any,
};
