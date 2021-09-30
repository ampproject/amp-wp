/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useNormalizedThemesData } from '../themes-context-provider/use-normalized-themes-data';
import { IconWebsitePaintBrush } from '../svg/website-paint-brush';
import { SiteScanResults } from './index';

/**
 * Renders a list of themes that cause issues.
 *
 * @param {Object} props                   Component props.
 * @param {Array}  props.issues            List of theme issues.
 * @param {Array}  props.validatedUrlsLink URL to the Validated URLs page.
 */
export function ThemesWithIssues( { issues = [], validatedUrlsLink } ) {
	const themesData = useNormalizedThemesData();
	const sources = issues?.map( ( slug ) => themesData?.[ slug ] ?? { name: slug } ) || [];

	return (
		<SiteScanResults
			title={ __( 'Themes with AMP incompatibilty', 'amp' ) }
			icon={ <IconWebsitePaintBrush /> }
			count={ issues.length }
			sources={ sources }
			validatedUrlsLink={ validatedUrlsLink }
		/>
	);
}

ThemesWithIssues.propTypes = {
	issues: PropTypes.array.isRequired,
	validatedUrlsLink: PropTypes.string,
};
