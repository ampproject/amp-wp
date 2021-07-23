/**
 * Summarize sources.
 *
 * @param {Array} sources Sources.
 * @return {Object|null} Summarized (de-duped) sources.
 */
export default function summarizeSources( sources ) {
	if ( ! sources || ! Array.isArray( sources ) || sources.length === 0 ) {
		return null;
	}

	const summarizedSources = sources.reduce( ( acc, source ) => {
		const { hook, type, name, embed, block_name: blockName } = source;

		if ( hook ) {
			acc.hook = hook;
		}

		if ( type && name && ! acc[ type ]?.includes( name ) ) {
			acc[ type ] = [
				...acc[ type ] ?? [],
				name,
			];
		} else if ( embed ) {
			acc.embed = true;
		}

		if ( blockName ) {
			acc.blocks = [
				...acc.blocks ?? [],
				blockName,
			];
		}

		return acc;
	}, {} );

	const {
		plugin,
		'mu-plugin': muPlugin,
		theme,
		core,
		embed,
		blocks,
		hook,
	} = summarizedSources;

	// Return only plugins and themes if they are present.
	if ( plugin || muPlugin || theme ) {
		const result = {};

		if ( plugin ) {
			result.plugin = plugin;
		}
		if ( muPlugin ) {
			result.muPlugin = muPlugin;
		}
		if ( theme && ! embed ) {
			result.theme = theme;
		}

		return result;
	}

	// Return core if there are no plugins or themes.
	if ( core ) {
		return { core };
	}

	// Return embed if there is no plugin, theme or core.
	if ( embed ) {
		return { embed };
	}

	// Return block if there is no plugin, theme, core or embed.
	if ( blocks ) {
		return { blocks };
	}

	// Return hook if there is no plugin, theme, core, embed or blocks.
	if ( hook ) {
		return { hook };
	}

	return null;
}
