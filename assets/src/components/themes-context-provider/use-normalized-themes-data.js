/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Themes } from './index';

export function useNormalizedThemesData() {
	const { fetchingThemes, themes } = useContext( Themes );
	const [ normalizedThemesData, setNormalizedThemesData ] = useState( {} );

	useEffect( () => {
		if ( fetchingThemes || themes.length === 0 ) {
			return;
		}

		setNormalizedThemesData( () => themes.reduce( ( accumulatedThemesData, source ) => ( {
			...accumulatedThemesData,
			[ source.stylesheet ]: Object.keys( source ).reduce( ( props, key ) => {
				const normalizedData = {
					...props,
					slug: source.stylesheet,
					// Flatten every prop that contains a `raw` member.
					[ key ]: source[ key ]?.raw ?? source[ key ],
				};

				// Look for a child theme.
				const childTheme = themes.find( ( theme ) => theme.template === source.stylesheet && theme.template !== theme.stylesheet )?.stylesheet;
				if ( childTheme ) {
					normalizedData.child = childTheme;
				}

				// Set parent theme if we're dealing with a child theme.
				if ( source.template && source.template !== source.stylesheet ) {
					normalizedData.parent = source.template;
				}

				return normalizedData;
			}, {} ),
		} ), {} ) );
	}, [ fetchingThemes, themes ] );

	return normalizedThemesData;
}
