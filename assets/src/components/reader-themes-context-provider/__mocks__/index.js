/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

export const ReaderThemes = createContext();

/**
 * MOCK.
 *
 * @param {Object} props
 * @param {any} props.children Component children.
 * @param {boolean} props.downloadingTheme Whether downloading a theme or not.
 */
export function ReaderThemesContextProvider( { children, downloadingTheme = false } ) {
	return (
		<ReaderThemes.Provider value={
			{
				downloadingTheme,
			}
		}>
			{ children }
		</ReaderThemes.Provider>
	);
}
ReaderThemesContextProvider.propTypes = {
	children: PropTypes.any,
	downloadingTheme: PropTypes.bool,
};
