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
import { useNormalizedPluginsData } from '../plugins-context-provider/use-normalized-plugins-data';
import { IconLaptopPlug } from '../svg/laptop-plug';
import { SiteScanResults } from './index';

/**
 * Render a list of plugins that cause issues.
 *
 * @param {Object} props                   Component props.
 * @param {Array}  props.issues            List of plugins issues.
 * @param {Array}  props.validatedUrlsLink URL to the Validated URLs page.
 */
export function PluginsWithIssues( { issues = [], validatedUrlsLink, ...props } ) {
	const pluginsData = useNormalizedPluginsData();
	const sources = issues?.map( ( slug ) => pluginsData?.[ slug ] ?? { name: slug } ) || [];

	return (
		<SiteScanResults
			title={ __( 'Plugins with AMP incompatibility', 'amp' ) }
			icon={ <IconLaptopPlug /> }
			count={ issues.length }
			sources={ sources }
			validatedUrlsLink={ validatedUrlsLink }
			className="site-scan-results--plugins"
			{ ...props }
		/>
	);
}

PluginsWithIssues.propTypes = {
	issues: PropTypes.array.isRequired,
	validatedUrlsLink: PropTypes.string,
};
