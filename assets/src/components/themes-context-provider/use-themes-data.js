/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

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

	const getThemeNameBySlug = ( slug ) => nameBySlug[ slug ] ??
		sprintf(
			/* translators: Theme slug. */
			__( 'Theme: %s', 'amp' ),
			slug,
		);

	return {
		getThemeNameBySlug,
		themes,
	};
}
