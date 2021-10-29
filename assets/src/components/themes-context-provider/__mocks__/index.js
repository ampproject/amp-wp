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
 * @param {any}     props.children       Component children.
 * @param {boolean} props.fetchingThemes Whether fetching themes or not.
 * @param {Array}   props.themes         An array of fetched themes.
 */
export function ThemesContextProvider( {
	children,
	fetchingThemes = false,
	themes = [],
} ) {
	return (
		<Themes.Provider value={
			{
				fetchingThemes,
				themes,
			}
		}>
			{ children }
		</Themes.Provider>
	);
}
ThemesContextProvider.propTypes = {
	children: PropTypes.any,
	fetchingThemes: PropTypes.bool,
	themes: PropTypes.array,
};
