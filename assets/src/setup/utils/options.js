/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export const Options = createContext();

export function OptionsContextProvider( { children } ) {
	return (
		<Options.Provider>
			{ children }
		</Options.Provider>
	);
}

OptionsContextProvider.propTypes = {
	children: PropTypes.any,
};
