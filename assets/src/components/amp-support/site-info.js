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
import { ListItems } from '../list-items';

/**
 * Render site information.
 *
 * @param {Object} props          Site info data.
 * @param {Object} props.siteInfo Site information data.
 * @return {JSX.Element} Site information markup.
 */
export function SiteInfo( { siteInfo } ) {
	if ( 'object' !== typeof siteInfo ) {
		return null;
	}

	return (
		<details open={ false }>
			<summary>
				{ __( 'Site Information', 'amp' ) }
			</summary>
			<div className="detail-body">
				<ListItems
					heading={ __( 'Site Information', 'amp' ) }
					items={ [
						{
							label: __( 'Site URL', 'amp' ),
							value: siteInfo?.site_url,
						},
						{
							label: __( 'Site title', 'amp' ),
							value: siteInfo?.site_title,
						},
						{
							label: __( 'PHP version', 'amp' ),
							value: siteInfo?.php_version,
						},
						{
							label: __( 'MySQL version', 'amp' ),
							value: siteInfo?.mysql_version,
						},
						{
							label: __( 'WordPress version', 'amp' ),
							value: siteInfo?.wp_version,
						},
						{
							label: __( 'WordPress language', 'amp' ),
							value: siteInfo?.wp_language,
						},
					] } />
				<ListItems
					heading={ __( 'Site Health', 'amp' ) }
					items={ [
						{
							label: __( 'Https status', 'amp' ),
							value: siteInfo?.wp_https_status ? 'Yes' : 'No',
						},
						{
							label: __( 'Object cache status', 'amp' ),
							value: siteInfo?.object_cache_status ? 'Yes' : 'No',
						},
						{
							label: __( 'Libxml version', 'amp' ),
							value: siteInfo?.libxml_version,
						},
						{
							label: __( 'Is defined curl multi', 'amp' ),
							value: siteInfo?.is_defined_curl_multi ? 'Yes' : 'No',
						},
					] } />
				<ListItems
					heading={ __( 'AMP Information', 'amp' ) }
					items={ [
						{
							label: __( 'AMP mode', 'amp' ),
							value: siteInfo?.amp_mode,
						},
						{
							label: __( 'AMP version', 'amp' ),
							value: siteInfo?.amp_version,
						},
						{
							label: __( 'AMP plugin configured', 'amp' ),
							value: siteInfo?.amp_plugin_configured ? 'Yes' : 'No',
						},
						{
							label: __( 'AMP all templates supported', 'amp' ),
							value: siteInfo?.amp_all_templates_supported ? 'Yes' : 'No',
						},
						{
							label: __( 'AMP supported post types', 'amp' ),
							value: siteInfo?.amp_supported_post_types ? siteInfo.amp_supported_post_types.join( ', ' ) : '',
						},
						{
							label: __( 'AMP supported templates', 'amp' ),
							value: siteInfo?.amp_supported_templates ? siteInfo.amp_supported_templates.join( ', ' ) : '',
						},
						{
							label: __( 'AMP mobile redirect', 'amp' ),
							value: siteInfo?.amp_mobile_redirect ? 'Yes' : 'No',
						},
						{
							label: __( 'AMP reader theme', 'amp' ),
							value: siteInfo?.amp_reader_theme,
						},
					] } />
			</div>
		</details>
	);
}

SiteInfo.propTypes = {
	siteInfo: PropTypes.object.isRequired,
};
