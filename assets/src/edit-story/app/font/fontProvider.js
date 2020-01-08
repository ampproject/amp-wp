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
import useLoadFontFiles from './actions/useLoadFontFiles';

function FontProvider( { children } ) {
	const [ fonts, setFonts ] = useState( [] );

	useLoadFonts( { fonts, setFonts } );

	const getFontBy = useCallback(
		( key, value ) => {
			const foundFont = fonts.find( ( thisFont ) => thisFont[ key ] === value );
			if ( ! foundFont ) {
				return {};
			}
			return foundFont;
		},	[ fonts ],
	);

	const getFontByName = useCallback(
		( name ) => {
			return getFontBy( 'name', name );
		},	[ getFontBy ],
	);

	const getFontBySlug = useCallback(
		( slug ) => {
			return getFontBy( 'slug', slug );
		},	[ getFontBy ],
	);

	const getFontWeight = useCallback(
		( name ) => {
			const fontWeightNames = {
				100: 'Hairline',
				200: 'Thin',
				300: 'Light',
				400: 'Normal',
				500: 'Medium',
				600: 'Semi bold',
				700: 'Bold',
				800: 'Extra bold',
				900: 'Super bold',
			};

			const defaultFontWeights = [
				{ name: fontWeightNames[ 400 ], slug: 400, thisValue: 400 },
			];

			const currentFont = getFontByName( name );
			let fontWeights = defaultFontWeights;
			if ( currentFont ) {
				const { weights } = currentFont;
				if ( weights ) {
					fontWeights = weights.map( ( weight ) => ( { name: fontWeightNames[ weight ], slug: weight, thisValue: weight } ) );
				}
			}
			return fontWeights;
		},	[ getFontByName ],
	);

	const maybeEnqueueFontStyle = useLoadFontFiles( { getFontByName } );

	const state = {
		state: {
			fonts,
		},
		actions: {
			getFontByName,
			getFontBySlug,
			maybeEnqueueFontStyle,
			getFontWeight,
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
