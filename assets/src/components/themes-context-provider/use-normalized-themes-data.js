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
	const [ normalizedThemesData, setNormalizedThemesData ] = useState( [] );

	useEffect( () => {
		if ( fetchingThemes || themes.length === 0 ) {
			return;
		}

		setNormalizedThemesData( () => themes.reduce( ( accumulatedThemesData, source ) => ( {
			...accumulatedThemesData,
			[ source.stylesheet ]: Object.keys( source ).reduce( ( props, key ) => ( {
				...props,
				slug: source.stylesheet,
				// Flatten every prop that contains a `raw` member.
				[ key ]: source[ key ]?.raw ?? source[ key ],
			} ), {} ),
		} ), {} ) );
	}, [ fetchingThemes, themes ] );

	return normalizedThemesData;
}
