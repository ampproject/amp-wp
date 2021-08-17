/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

export const Themes = createContext();

/**
 * MOCK.
 *
 * @param {Object}  props
 * @param {any}     props.children
 */
export function ThemesContextProvider( { children } ) {
	return (
		<Themes.Provider value={
			{
				fetchingThemes: false,
				themes: [],
			}
		}>
			{ children }
		</Themes.Provider>
	);
}
ThemesContextProvider.propTypes = {
	children: PropTypes.any,
};
