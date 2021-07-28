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

export default function SourceLabel( {
	sources,
	type,
	isCodeOutput,
} ) {
	let icon;
	let title;
	let singleTitle = sources?.[ 0 ];

	switch ( type ) {
		case SOURCE_TYPE_PLUGIN:
			icon = 'admin-plugins';
			title = __( 'Plugins', 'amp' );
			break;
		case SOURCE_TYPE_MU_PLUGIN:
			icon = 'admin-plugins';
			title = __( 'Must-Use Plugins', 'amp' );
			break;
		case SOURCE_TYPE_THEME:
			icon = 'admin-appearance';
			title = singleTitle ?? __( 'Theme', 'amp' );
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

	if ( ! sources || sources.length === 1 ) {
		return (
			<strong className="source">
				<Dashicon icon={ icon } />
				{ isCodeOutput ? (
					<code>
						{ singleTitle ?? title }
					</code>
				) : singleTitle ?? title }
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
