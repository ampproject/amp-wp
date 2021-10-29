/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

export const Plugins = createContext();

/**
 * MOCK.
 *
 * @param {Object}  props
 * @param {any}     props.children        Component children.
 * @param {boolean} props.fetchingPlugins Whether fetching plugins or not.
 * @param {Array}   props.plugins         An array of fetched plugins.
 */
export function PluginsContextProvider( {
	children,
	fetchingPlugins = false,
	plugins = [],
} ) {
	return (
		<Plugins.Provider value={
			{
				fetchingPlugins,
				plugins,
			}
		}>
			{ children }
		</Plugins.Provider>
	);
}
PluginsContextProvider.propTypes = {
	children: PropTypes.any,
	fetchingPlugins: PropTypes.bool,
	plugins: PropTypes.array,
};
