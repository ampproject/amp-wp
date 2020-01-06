/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

import useLoadFonts from './effects/useLoadFonts';

function FontProvider( { children } ) {
	const [ fonts, setFonts ] = useState( [] );

	useLoadFonts( { fonts, setFonts } );

	const getFont = useCallback(
		( name ) => {
			const foundFont = fonts.find( ( thisFont ) => thisFont.name === name );
			if ( ! foundFont ) {
				return {};
			}
			return foundFont;
		},	[ fonts ],
	);

	const state = {
		state: {
			fonts,
		},
		actions: {
			getFont,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

FontProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default FontProvider;
