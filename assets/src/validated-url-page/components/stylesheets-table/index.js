/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import StylesheetsTableHead from './head';
import StylesheetsTableRow from './row';

export default function StylesheetsTable( {
	environment,
	stylesheets,
	stats,
} ) {
	const totalFinalSize = stats.included.finalSize + stats.excluded.finalSize;
	const validatedTheme = environment?.theme ? Object.keys( environment.theme )[ 0 ] : '';

	return (
		<table className="amp-stylesheet-list wp-list-table widefat fixed striped">
			<StylesheetsTableHead />
			<tbody>
				{ stylesheets.map( ( stylesheet, index ) => (
					<StylesheetsTableRow
						finalSize={ stylesheet.final_size }
						index={ index }
						isExcessive={ stats.excessive.stylesheets.includes( stylesheet.hash ) }
						isExcluded={ stats.excluded.stylesheets.includes( stylesheet.hash ) }
						isIncluded={ stats.included.stylesheets.includes( stylesheet.hash ) }
						key={ stylesheet.hash }
						originalSize={ stylesheet.original_size }
						orignalTag={ stylesheet.original_tag }
						orignalTagAbbr={ stylesheet.original_tag_abbr }
						priority={ stylesheet.priority }
						shakenTokens={ stylesheet.shaken_tokens }
						sources={ stylesheet.sources }
						totalFinalSize={ totalFinalSize }
						validatedTheme={ validatedTheme }
					/>
				) ) }
			</tbody>
		</table>
	);
}
StylesheetsTable.propTypes = {
	environment: PropTypes.shape( {
		theme: PropTypes.object,
	} ),
	stylesheets: PropTypes.arrayOf( PropTypes.shape( {
		final_size: PropTypes.number,
		hash: PropTypes.string,
		original_size: PropTypes.number,
		original_tag: PropTypes.string,
		original_tag_abbr: PropTypes.string,
		priority: PropTypes.number,
		shaken_tokens: PropTypes.array,
		sources: PropTypes.array,
	} ) ),
	stats: PropTypes.shape( {
		included: PropTypes.shape( {
			finalSize: PropTypes.number,
			stylesheets: PropTypes.array,
		} ),
		excluded: PropTypes.shape( {
			finalSize: PropTypes.number,
			stylesheets: PropTypes.array,
		} ),
		excessive: PropTypes.shape( {
			stylesheets: PropTypes.array,
		} ),
	} ),
};
