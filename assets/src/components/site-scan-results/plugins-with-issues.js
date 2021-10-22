/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useNormalizedPluginsData } from '../plugins-context-provider/use-normalized-plugins-data';
import { IconLaptopPlug } from '../svg/laptop-plug';
import { SiteScanSourcesList } from './site-scan-sources-list';
import { SiteScanResults } from './index';

/**
 * Render a list of plugins that cause issues.
 *
 * @param {Object} props           Component props.
 * @param {string} props.className Component class name.
 * @param {Array}  props.issues    List of plugins issues.
 */
export function PluginsWithIssues( { issues = [], className, ...props } ) {
	const pluginsData = useNormalizedPluginsData();
	const sources = useMemo( () => issues?.map( ( slug ) => pluginsData?.[ slug ] ?? {
		slug,
		status: 'uninstalled',
	} ) || [], [ issues, pluginsData ] );

	return (
		<SiteScanResults
			title={ __( 'Plugins with AMP incompatibility', 'amp' ) }
			icon={ <IconLaptopPlug /> }
			count={ issues.length }
			className={ classnames( 'site-scan-results--plugins', className ) }
			{ ...props }
		>
			<SiteScanSourcesList
				sources={ sources }
				inactiveSourceNotice={ __( 'This plugin has been deactivated since last site scan.' ) }
				uninstalledSourceNotice={ __( 'This plugin has been uninstalled since last site scan.' ) }
			/>
		</SiteScanResults>
	);
}

PluginsWithIssues.propTypes = {
	className: PropTypes.string,
	issues: PropTypes.array.isRequired,
};
