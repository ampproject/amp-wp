/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Dashicon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	SOURCE_TYPE_PLUGIN,
	SOURCE_TYPE_MU_PLUGIN,
	SOURCE_TYPE_THEME,
	SOURCE_TYPE_CORE,
	SOURCE_TYPE_EMBED,
	SOURCE_TYPE_BLOCK,
	SOURCE_TYPE_HOOK,
	SOURCE_TYPE_HOOK_THE_CONTENT,
	SOURCE_TYPE_HOOK_THE_EXCERPT,
} from '../../utils/sources';
import usePluginsData from '../plugins-context-provider/use-plugins-data';
import useThemesData from '../themes-context-provider/use-themes-data';

export default function SourceLabel( {
	sources = [],
	type,
	isCodeOutput,
} ) {
	const { getPluginNameBySlug } = usePluginsData();
	const { getThemeNameBySlug } = useThemesData();

	let icon;
	let title;
	let singleTitle;

	switch ( type ) {
		case SOURCE_TYPE_PLUGIN:
			icon = 'admin-plugins';
			title = __( 'Plugins', 'amp' );
			sources = sources.map( ( slug ) => getPluginNameBySlug( slug ) );
			break;
		case SOURCE_TYPE_MU_PLUGIN:
			icon = 'admin-plugins';
			title = __( 'Must-Use Plugins', 'amp' );
			sources = sources.map( ( slug ) => getPluginNameBySlug( slug ) );
			break;
		case SOURCE_TYPE_THEME:
			icon = 'admin-appearance';
			sources = sources.map( ( slug ) => getThemeNameBySlug( slug ) ?? __( 'Theme', 'amp' ) );
			break;
		case SOURCE_TYPE_CORE:
			icon = 'wordpress-alt';
			title = __( 'Core', 'amp' );
			break;
		case SOURCE_TYPE_EMBED:
			icon = 'wordpress-alt';
			title = __( 'Embed', 'amp' );
			break;
		case SOURCE_TYPE_HOOK:
			switch ( sources[ 0 ] ) {
				case SOURCE_TYPE_HOOK_THE_CONTENT:
					icon = 'edit';
					singleTitle = __( 'Content', 'amp' );
					break;
				case SOURCE_TYPE_HOOK_THE_EXCERPT:
					icon = 'edit';
					singleTitle = __( 'Excerpt', 'amp' );
					break;
				default:
					icon = 'wordpress-alt';
					singleTitle = sprintf(
						// translators: placeholder is the hook name.
						__( 'Hook: %s', 'amp' ), sources[ 0 ],
					);
			}
			break;
		case SOURCE_TYPE_BLOCK:
			icon = 'edit';
			break;
		default:
			return null;
	}

	if ( ! sources || sources.length <= 1 ) {
		return (
			<strong className="source">
				<Dashicon icon={ icon } />
				{ isCodeOutput ? (
					<code>
						{ singleTitle ?? sources?.[ 0 ] ?? title }
					</code>
				) : singleTitle ?? sources?.[ 0 ] ?? title }
			</strong>
		);
	}

	return (
		<details className="source">
			<summary className="details-attributes__summary">
				<strong className="source">
					<Dashicon icon={ icon } />
					{ title }
					{ ` (${ sources.length })` }
				</strong>
			</summary>
			{ sources.map( ( item ) => (
				<div key={ item }>
					{ isCodeOutput ? (
						<code>
							{ item }
						</code>
					) : item }
				</div>
			) ) }
		</details>
	);
}
SourceLabel.propTypes = {
	isCodeOutput: PropTypes.bool,
	sources: PropTypes.array,
	type: PropTypes.oneOf( [
		SOURCE_TYPE_PLUGIN,
		SOURCE_TYPE_MU_PLUGIN,
		SOURCE_TYPE_THEME,
		SOURCE_TYPE_CORE,
		SOURCE_TYPE_EMBED,
		SOURCE_TYPE_BLOCK,
		SOURCE_TYPE_HOOK,
	] ),
};
