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
 * @param {any}     props.children
 */
export function PluginsContextProvider( { children } ) {
	return (
		<Plugins.Provider value={
			{
				fetchingPlugins: false,
				plugins: [],
			}
		}>
			{ children }
		</Plugins.Provider>
	);
}
PluginsContextProvider.propTypes = {
	children: PropTypes.any,
};
