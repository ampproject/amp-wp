/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	SOURCE_TYPE_THEME,
	summarizeSources,
} from '../../utils/sources';
import SourceLabel from '../source-label';

export default function SourcesSummary( { sources, validatedTheme } ) {
	const summarizedSources = useMemo( () => {
		if ( ! sources || ! Array.isArray( sources ) || sources.length === 0 ) {
			return null;
		}

		return summarizeSources( sources );
	}, [ sources ] );

	if ( ! summarizedSources && validatedTheme ) {
		return (
			<SourceLabel sources={ validatedTheme } type={ SOURCE_TYPE_THEME } />
		);
	}

	if ( ! summarizedSources ) {
		return null;
	}

	return Object.keys( summarizedSources ).map( ( type ) => (
		<SourceLabel
			key={ type }
			type={ type }
			sources={ summarizedSources[ type ] }
		/>
	) );
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
