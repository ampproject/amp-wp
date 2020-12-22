/**
 * External dependencies
 */
import { pluginNames, themeName, themeSlug } from 'amp-block-validation';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Attempts to get the title of the plugin or theme responsible for an error.
 *
 * Adapted from AMP_Validated_URL_Post_Type::render_sources_column PHP method.
 *
 * @param {Object[]} sources Error source details from the PHP backtrace.
 */
export function getErrorSourceTitle( sources ) {
	const keyedSources = { theme: [], plugin: [], 'mu-plugin': [], embed: [], core: [], blocks: [] };
	for ( const source of sources ) {
		if ( source.type && source.type in keyedSources ) {
			keyedSources[ source.type ].push( source );
		} else if ( 'block_name' in source ) {
			keyedSources.blocks.push( source );
		}
	}

	const output = [];
	const uniquePluginNames = [ ...new Set( keyedSources.plugin.map( ( { name } ) => name ) ) ];
	const muPluginNames = [ ...new Set( keyedSources[ 'mu-plugin' ].map( ( { name } ) => name ) ) ];
	const combinedPluginNames = [ ...uniquePluginNames, ...muPluginNames ];

	if ( 1 === combinedPluginNames.length ) {
		output.push( pluginNames[ combinedPluginNames[ 0 ] ] || combinedPluginNames[ 0 ] );
	} else {
		const pluginCount = uniquePluginNames.length;
		const muPluginCount = muPluginNames.length;

		if ( 0 < pluginCount ) {
			output.push( sprintf( '%1$s (%2$d)', __( 'Plugins', 'amp' ), pluginCount ) );
		}

		if ( 0 < muPluginCount ) {
			output.push( sprintf( '%1$s (%2$d)', __( 'Must-use plugins', 'amp' ), muPluginCount ) );
		}
	}

	if ( 0 === keyedSources.embed.length ) {
		const activeThemeSources = keyedSources.theme.filter( ( { name } ) => themeSlug === name );
		const inactiveThemeSources = keyedSources.theme.filter( ( { name } ) => themeSlug !== name );
		if ( 0 < activeThemeSources.length ) {
			output.push( themeName );
		}

		if ( 0 < inactiveThemeSources.length ) {
			/* translators: placeholder is the slug of an inactive WordPress theme. */
			output.push( __( 'Inactive theme(s)', 'amp' ) );
		}
	}

	if ( 0 === output.length && 0 < keyedSources.blocks.length ) {
		output.push( keyedSources.blocks[ 0 ].block_name );
	}

	if ( 0 === output.length && 0 < keyedSources.embed.length ) {
		output.push( __( 'Embed', 'amp' ) );
	}

	if ( 0 === output.length && 0 < keyedSources.core.length ) {
		output.push( __( 'Core', 'amp' ) );
	}

	return output.join( ', ' );
}
