/**
 * Summarize sources.
 *
 * @param {Array} sources Sources.
 * @return {Object} Summarized (de-duped) sources.
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

	// Remove core if there is a plugin or theme.
	if ( summarizedSources?.core && ( summarizedSources?.theme || summarizedSources?.plugin ) ) {
		delete summarizedSources.core;
	}

	return summarizedSources;
}
