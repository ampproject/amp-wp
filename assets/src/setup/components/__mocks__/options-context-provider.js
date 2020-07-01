/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useState } from '@wordpress/element';

export const Options = createContext();

/**
 * MOCK.
 *
 * @param {Object} props
 * @param {any} props.children Component children.
 */
export function OptionsContextProvider( { children } ) {
	const [ updates, updateOptions ] = useState( {
		redirect_toggle: false,
	} );

	return (
		<Options.Provider value={
			{
				originalOptions: {
					theme_support: 'some-support',
				},
				updates: updates || {},
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
