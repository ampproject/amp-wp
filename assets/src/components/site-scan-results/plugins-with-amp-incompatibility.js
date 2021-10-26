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
 * Render a list of plugins that cause AMP Incompatibility.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.className Component class name.
 * @param {string[]} props.slugs     List of plugins slugs.
 */
export function PluginsWithAmpIncompatibility( { className, slugs = [], ...props } ) {
	const pluginsData = useNormalizedPluginsData();
	const sources = useMemo( () => slugs?.map( ( slug ) => pluginsData?.[ slug ] ?? {
		slug,
		status: 'uninstalled',
	} ) || [], [ pluginsData, slugs ] );

	return (
		<SiteScanResults
			title={ __( 'Plugins with AMP incompatibility', 'amp' ) }
			icon={ <IconLaptopPlug /> }
			count={ slugs.length }
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

PluginsWithAmpIncompatibility.propTypes = {
	className: PropTypes.string,
	slugs: PropTypes.arrayOf( PropTypes.string ).isRequired,
};
