/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

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
				100: __( 'Hairline', 'amp' ),
				200: __( 'Thin', 'amp' ),
				300: __( 'Light', 'amp' ),
				400: __( 'Normal', 'amp' ),
				500: __( 'Medium', 'amp' ),
				600: __( 'Semi bold', 'amp' ),
				700: __( 'Bold', 'amp' ),
				800: __( 'Extra bold', 'amp' ),
				900: __( 'Super bold', 'amp' ),
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

	const getFontFallback = useCallback(
		( name ) => {
			const currentFont = getFontByName( name );
			const fontFallback = ( currentFont && currentFont.fallbacks ) ? currentFont.fallbacks : [];
			return fontFallback;
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
			getFontFallback,
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
