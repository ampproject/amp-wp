/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Themes } from './index';

export function useNormalizedThemesData( { skipInactive = true } = {} ) {
	const { fetchingThemes, themes } = useContext( Themes );
	const [ normalizedThemesData, setNormalizedThemesData ] = useState( [] );

	useEffect( () => {
		if ( fetchingThemes || themes.length === 0 ) {
			return;
		}

		setNormalizedThemesData( () => themes.reduce( ( acc, source ) => {
			const { status, stylesheet } = source;

			if ( ! stylesheet ) {
				return acc;
			}

			if ( skipInactive && status !== 'active' ) {
				return acc;
			}

			return {
				...acc,
				[ stylesheet ]: Object.keys( source ).reduce( ( props, key ) => ( {
					...props,
					// Flatten every prop that contains a `raw` member.
					[ key ]: source[ key ]?.raw ?? source[ key ],
				} ), {} ),
			};
		}, {} ) );
	}, [ skipInactive, fetchingThemes, themes ] );

	return normalizedThemesData;
}
