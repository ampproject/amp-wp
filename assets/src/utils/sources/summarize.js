/**
 * Internal dependencies
 */
import {
	SOURCE_TYPE_BLOCK,
	SOURCE_TYPE_CORE,
	SOURCE_TYPE_EMBED,
	SOURCE_TYPE_HOOK,
	SOURCE_TYPE_MU_PLUGIN,
	SOURCE_TYPE_PLUGIN,
	SOURCE_TYPE_THEME,
} from './index';

/**
 * Summarize sources.
 *
 * @param {Array} raw Raw sources.
 * @return {Object|null} Summarized (de-duped) sources.
 */
export default function summarizeSources( raw ) {
	if ( ! raw || ! Array.isArray( raw ) || raw.length === 0 ) {
		return null;
	}

	const sources = raw.reduce( ( acc, source ) => {
		const { hook, type, name, embed, block_name: blockName } = source;

		if ( hook ) {
			acc[ SOURCE_TYPE_HOOK ] = hook;
		}

		if ( type && name && ! acc[ type ]?.includes( name ) ) {
			acc[ type ] = [
				...acc[ type ] ?? [],
				name,
			];
		} else if ( embed ) {
			acc[ SOURCE_TYPE_EMBED ] = true;
		}

		if ( blockName ) {
			acc[ SOURCE_TYPE_BLOCK ] = [
				...acc[ SOURCE_TYPE_BLOCK ] ?? [],
				blockName,
			];
		}

		return acc;
	}, {} );

	// Return only plugins and themes if they are present.
	if (
		sources[ SOURCE_TYPE_PLUGIN ] ||
		sources[ SOURCE_TYPE_MU_PLUGIN ] ||
		sources[ SOURCE_TYPE_THEME ]
	) {
		const result = {};

		if ( sources[ SOURCE_TYPE_PLUGIN ] ) {
			result[ SOURCE_TYPE_PLUGIN ] = sources[ SOURCE_TYPE_PLUGIN ];

			// Skip including Gutenberg in the summary if there is another plugin, since Gutenberg is like core.
			if ( result[ SOURCE_TYPE_PLUGIN ].length > 1 && result[ SOURCE_TYPE_PLUGIN ].includes( 'gutenberg' ) ) {
				result[ SOURCE_TYPE_PLUGIN ] = result[ SOURCE_TYPE_PLUGIN ].filter( ( plugin ) => plugin !== 'gutenberg' );
			}
		}
		if ( sources[ SOURCE_TYPE_MU_PLUGIN ] ) {
			result[ SOURCE_TYPE_MU_PLUGIN ] = sources[ SOURCE_TYPE_MU_PLUGIN ];
		}
		if ( sources[ SOURCE_TYPE_THEME ] && ! sources[ SOURCE_TYPE_EMBED ] ) {
			result[ SOURCE_TYPE_THEME ] = sources[ SOURCE_TYPE_THEME ];
		}

		return result;
	}

	// Return core if there are no plugins or themes.
	if ( sources[ SOURCE_TYPE_CORE ] ) {
		return {
			[ SOURCE_TYPE_CORE ]: sources[ SOURCE_TYPE_CORE ],
		};
	}

	// Return embed if there is no plugin, theme or core.
	if ( sources[ SOURCE_TYPE_EMBED ] ) {
		return {
			[ SOURCE_TYPE_EMBED ]: sources[ SOURCE_TYPE_EMBED ],
		};
	}

	// Return block if there is no plugin, theme, core or embed.
	if ( sources[ SOURCE_TYPE_BLOCK ] ) {
		return {
			[ SOURCE_TYPE_BLOCK ]: sources[ SOURCE_TYPE_BLOCK ],
		};
	}

	// Return hook if there is no plugin, theme, core, embed or blocks.
	if ( sources[ SOURCE_TYPE_HOOK ] ) {
		return {
			[ SOURCE_TYPE_HOOK ]: sources[ SOURCE_TYPE_HOOK ],
		};
	}

	return null;
}
