/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

export const User = createContext();

export function UserContextProvider( { children } ) {
	return (
		<User.Provider value={
			{
				savingDeveloperToolsOption: false,
			}
		}>
			{ children }
		</User.Provider>
	);
}
UserContextProvider.propTypes = {
	children: PropTypes.any,
};
