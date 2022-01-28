/**
 * WordPress dependencies
 */
import { createInterpolateElement, useContext } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { AMP_COMPATIBLE_THEMES_URL } from 'amp-site-scan-notice'; // From WP inline script.

/**
 * Internal dependencies
 */
import { Themes } from '../../components/themes-context-provider';
import { isExternalUrl } from '../../common/helpers/is-external-url';

/**
 * Render a message saying a theme is not AMP compatible.
 *
 * @param {Object} props                             Component props.
 * @param {Object} props.themeWithAmpIncompatibility Theme with AMP incompatibilities.
 */
export function ThemeWithAmpIncompatibility( { themeWithAmpIncompatibility } ) {
	const { fetchingThemes, themes } = useContext( Themes );

	if ( fetchingThemes ) {
		return null;
	}

	const themeMeta = themes.find( ( theme ) => theme.stylesheet === themeWithAmpIncompatibility.slug );
	const themeName = themeMeta?.name?.rendered ?? themeMeta?.name;

	return (
		<>
			<p>
				{ createInterpolateElement(
					sprintf(
						// translators: %s stands for a theme name.
						__( 'AMP compatibility issues discovered with the <b>%s</b> theme.', 'amp' ),
						themeName,
					),
					{
						b: <strong />,
					},
				) }
			</p>
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

ThemeWithAmpIncompatibility.propTypes = {
	themeWithAmpIncompatibility: PropTypes.shape( {
		slug: PropTypes.string,
		urls: PropTypes.arrayOf( PropTypes.string ),
	} ).isRequired,
};
