/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

export const User = createContext();

/**
 * MOCK.
 *
 * @param {Object}  props
 * @param {any}     props.children
 * @param {boolean} props.fetchingUser
 */
export function UserContextProvider( { children, fetchingUser } ) {
	return (
		<User.Provider value={
			{
				savingDeveloperToolsOption: false,
				fetchingUser,
			}
		}>
			{ children }
		</User.Provider>
	);
}
UserContextProvider.propTypes = {
	children: PropTypes.any,
	fetchingUser: PropTypes.bool,
};
