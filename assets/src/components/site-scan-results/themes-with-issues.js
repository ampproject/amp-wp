/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

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
 * @param {string} props.className         Component class name.
 * @param {Array}  props.issues            List of theme issues.
 * @param {string} props.validatedUrlsLink URL to the Validated URLs page.
 */
export function ThemesWithIssues( { issues = [], validatedUrlsLink, className, ...props } ) {
	const themesData = useNormalizedThemesData();
	const sources = issues?.map( ( slug ) => themesData?.[ slug ] ?? { name: slug } ) || [];

	return (
		<SiteScanResults
			title={ __( 'Themes with AMP incompatibility', 'amp' ) }
			icon={ <IconWebsitePaintBrush /> }
			count={ issues.length }
			sources={ sources }
			validatedUrlsLink={ validatedUrlsLink }
			className={ classnames( 'site-scan-results--themes', className ) }
			{ ...props }
		/>
	);
}

ThemesWithIssues.propTypes = {
	className: PropTypes.string,
	issues: PropTypes.array.isRequired,
	validatedUrlsLink: PropTypes.string,
};
