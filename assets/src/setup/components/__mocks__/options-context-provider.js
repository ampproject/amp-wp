/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useState } from '@wordpress/element';

export const Options = createContext();

export function OptionsContextProvider( { children } ) {
	const [ options, updateOptions ] = useState( {
		redirect_toggle: false,
	} );

	return (
		<Options.Provider value={
			{
				options: options || {},
				updateOptions,
			}
		}>
			{ children }
		</Options.Provider>
	);
}
OptionsContextProvider.propTypes = {
	children: PropTypes.any,
};
