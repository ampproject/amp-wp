/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import summarizeSources from '../../utils/summarize-sources';
import SourceLabel from '../source-label';

export default function SourcesSummary( { sources, validatedTheme } ) {
	if ( ! sources || ! Array.isArray( sources ) || sources.length === 0 ) {
		return null;
	}

	const summarizedSources = summarizeSources( sources );

	if ( ! summarizedSources && validatedTheme ) {
		return (
			<SourceLabel source={ validatedTheme } isTheme={ true } />
		);
	}

	const {
		plugin,
		muPlugin,
		theme,
		core,
		embed,
		blocks,
		hook,
	} = summarizedSources;

	return (
		<>
			{ plugin && <SourceLabel source={ plugin } isPlugin={ true } /> }
			{ muPlugin && <SourceLabel source={ muPlugin } isMuPlugin={ true } /> }
			{ theme && <SourceLabel source={ theme } isTheme={ true } /> }
			{ core && <SourceLabel source={ core } isCore={ true } /> }
			{ embed && <SourceLabel source={ embed } isEmbed={ true } /> }
			{ blocks && <SourceLabel source={ blocks } isBlock={ true } /> }
			{ hook && <SourceLabel source={ hook } isHook={ true } /> }
		</>
	);
}
SourcesSummary.propTypes = {
	sources: PropTypes.arrayOf(
		PropTypes.shape( {
			type: PropTypes.string,
			name: PropTypes.string,
			file: PropTypes.string,
			line: PropTypes.number,
			function: PropTypes.string,
			hook: PropTypes.string,
			priority: PropTypes.number,
			dependency_type: PropTypes.string,
			handle: PropTypes.string,
			text: PropTypes.string,
			extra_key: PropTypes.string,
			embed: PropTypes.string,
			block_name: PropTypes.string,
		} ),
	).isRequired,
	validatedTheme: PropTypes.string,
};
