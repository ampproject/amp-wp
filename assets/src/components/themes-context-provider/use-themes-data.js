/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Themes } from './index';

export default function useThemesData() {
	const { themes, fetchingThemes } = useContext( Themes );
	const [ nameBySlug, setNameBySlug ] = useState( [] );

	useEffect( () => {
		if ( fetchingThemes || ! themes ) {
			return;
		}

		setNameBySlug( () => themes.reduce( ( acc, theme ) => ( {
			...acc,
			[ theme.stylesheet ]: theme.name.raw,
		} ), {} ) );
	}, [ fetchingThemes, themes ] );

	const getThemeNameBySlug = ( slug ) => nameBySlug[ slug ] ?? slug;

	return {
		getThemeNameBySlug,
		themes,
	};
}
