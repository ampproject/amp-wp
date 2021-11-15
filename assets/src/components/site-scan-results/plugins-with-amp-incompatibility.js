/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { AMP_COMPATIBLE_PLUGINS_URL } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useMemo } from '@wordpress/element';
import { ExternalLink } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useNormalizedPluginsData } from '../plugins-context-provider/use-normalized-plugins-data';
import { IconLaptopPlug } from '../svg/laptop-plug';
import { isExternalUrl } from '../../common/helpers/is-external-url';
import { SiteScanSourcesList } from './site-scan-sources-list';
import { SiteScanResults } from './index';

/**
 * Render a list of plugins that cause AMP Incompatibility.
 *
 * @param {Object}   props              Component props.
 * @param {string}   props.className    Component class name.
 * @param {boolean}  props.showHelpText Show additional help text above the issues list.
 * @param {string[]} props.slugs        List of plugins slugs.
 */
export function PluginsWithAmpIncompatibility( {
	className,
	showHelpText = false,
	slugs = [],
	...props
} ) {
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
			{ showHelpText && (
				<p>
					{ createInterpolateElement(
						__( 'Because of plugin issues we’ve uncovered, you may want to <a>review and suppress plugins</a>.', 'amp' ),
						{
							// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
							a: <a href="#plugin-suppression" />,
						},
					) }
					{ AMP_COMPATIBLE_PLUGINS_URL ? createInterpolateElement(
						` ${ __( 'You may also want to browse <a>AMP compatible plugins</a>.', 'amp' ) }`,
						{
							a: isExternalUrl( AMP_COMPATIBLE_PLUGINS_URL )
								? <ExternalLink href={ AMP_COMPATIBLE_PLUGINS_URL } />
								// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
								: <a href={ AMP_COMPATIBLE_PLUGINS_URL } />,
						},
					) : '' }
				</p>
			) }
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
	showHelpText: PropTypes.bool,
	slugs: PropTypes.arrayOf( PropTypes.string ).isRequired,
};
