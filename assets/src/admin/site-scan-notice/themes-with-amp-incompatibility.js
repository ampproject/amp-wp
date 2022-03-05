/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { AMP_COMPATIBLE_THEMES_URL } from 'amp-site-scan-notice'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { useContext, useMemo } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Themes } from '../../components/themes-context-provider';
import { isExternalUrl } from '../../common/helpers/is-external-url';

/**
 * Render a DETAILS element for each theme causing AMP incompatibilities.
 *
 * @param {Object} props                              Component props.
 * @param {Array}  props.themesWithAmpIncompatibility Themes with AMP incompatibilities.
 */
export function ThemesWithAmpIncompatibility( { themesWithAmpIncompatibility } ) {
	const { fetchingThemes, themes } = useContext( Themes );

	const themeNames = useMemo(
		() => themes?.reduce( ( accumulatedThemeNames, theme ) => ( {
			...accumulatedThemeNames,
			[ theme.stylesheet ]: theme.name?.rendered ?? theme.name,
		} ), {} ),
		[ themes ],
	);

	if ( fetchingThemes ) {
		return null;
	}

	return (
		<>
			<p>
				{ _n(
					'AMP compatibility issues discovered with the following theme:',
					'AMP compatibility issues discovered with the following themes:',
					themesWithAmpIncompatibility.length,
					'amp',
				) }
			</p>
			{ themesWithAmpIncompatibility.map( ( themeWithAmpIncompatibility ) => (
				<details
					key={ themeWithAmpIncompatibility.slug }
					className="amp-site-scan-notice__source-details"
				>
					<summary
						className="amp-site-scan-notice__source-summary"
						dangerouslySetInnerHTML={ {
							__html: sprintf(
								/* translators: 1: theme name; 2: number of URLs with AMP validation issues. */
								_n(
									'<b>%1$s</b> on %2$d URL',
									'<b>%1$s</b> on %2$d URLs',
									themeWithAmpIncompatibility.urls.length,
									'amp',
								),
								themeNames[ themeWithAmpIncompatibility.slug ],
								themeWithAmpIncompatibility.urls.length,
							),
						} }
					/>
					<ul className="amp-site-scan-notice__urls-list">
						{ themeWithAmpIncompatibility.urls.map( ( url ) => (
							<li key={ url }>
								<a href={ url } target="_blank" rel="noopener noreferrer">
									{ url }
								</a>
							</li>
						) ) }
					</ul>
				</details>
			) ) }
			<div className="amp-site-scan-notice__cta">
				<a
					href={ AMP_COMPATIBLE_THEMES_URL }
					className="button"
					{ ...isExternalUrl( AMP_COMPATIBLE_THEMES_URL ) ? { target: '_blank', rel: 'noopener noreferrer' } : {} }
				>
					{ __( 'View AMP-Compatible Themes', 'amp' ) }
				</a>
			</div>
		</>
	);
}

ThemesWithAmpIncompatibility.propTypes = {
	themesWithAmpIncompatibility: PropTypes.arrayOf(
		PropTypes.shape( {
			slug: PropTypes.string,
			urls: PropTypes.arrayOf( PropTypes.string ),
		} ),
	).isRequired,
};
