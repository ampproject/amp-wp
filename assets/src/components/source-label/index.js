/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Dashicon } from '@wordpress/components';

export default function SourceLabel( {
	source,
	isCodeOutput,
	isPlugin,
	isMuPlugin,
	isTheme,
	isCore,
	isEmbed,
	isHook,
	isBlock,
} ) {
	const sources = Array.isArray( source ) ? source : [ source ];

	let icon;
	let title;
	let singleTitle = sources?.[ 0 ];

	if ( isPlugin ) {
		icon = 'admin-plugins';
		title = __( 'Plugins', 'amp' );
	} else if ( isMuPlugin ) {
		icon = 'admin-plugins';
		title = __( 'Must-Use Plugins', 'amp' );
	} else if ( isTheme ) {
		icon = 'admin-appearance';
	} else if ( isCore ) {
		icon = 'wordpress-alt';
		title = __( 'Other', 'amp' );
	} else if ( isEmbed ) {
		icon = 'wordpress-alt';
		title = __( 'Embed', 'amp' );
	} else if ( isHook ) {
		switch ( sources[ 0 ] ) {
			case 'the_content':
				icon = 'edit';
				singleTitle = __( 'Content', 'amp' );
				break;
			case 'the_excerpt':
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
	} else if ( isBlock ) {
		icon = 'edit';
	}

	if ( ! icon ) {
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
	source: PropTypes.oneOfType( [ PropTypes.string, PropTypes.array ] ),
	isCodeOutput: PropTypes.bool,
	isPlugin: PropTypes.bool,
	isMuPlugin: PropTypes.bool,
	isTheme: PropTypes.bool,
	isCore: PropTypes.bool,
	isEmbed: PropTypes.bool,
	isHook: PropTypes.bool,
	isBlock: PropTypes.bool,
};
