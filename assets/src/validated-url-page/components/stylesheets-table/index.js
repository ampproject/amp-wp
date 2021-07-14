/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import StylesheetsTableHead from './head';
import StylesheetsTableRow from './row';

export default function StylesheetsTable( { stylesheets, stats } ) {
	const totalFinalSize = stats.included.finalSize + stats.excluded.finalSize;

	return (
		<table className="amp-stylesheet-list wp-list-table widefat fixed striped">
			<StylesheetsTableHead />
			<tbody>
				{ stylesheets.map( ( stylesheet, index ) => (
					<StylesheetsTableRow
						key={ stylesheet.hash }
						index={ index }
						originalSize={ stylesheet.original_size }
						finalSize={ stylesheet.final_size }
						totalFinalSize={ totalFinalSize }
						priority={ stylesheet.priority }
						orignalTag={ stylesheet.original_tag }
						orignalTagAbbr={ stylesheet.original_tag_abbr }
						isIncluded={ stats.included.stylesheets.includes( stylesheet.hash ) }
						isExcluded={ stats.excluded.stylesheets.includes( stylesheet.hash ) }
						isExcessive={ stats.excessive.stylesheets.includes( stylesheet.hash ) }
					/>
				) ) }
			</tbody>
		</table>
	);
}
StylesheetsTable.propTypes = {
	stylesheets: PropTypes.arrayOf( PropTypes.shape( {
		hash: PropTypes.string,
		original_size: PropTypes.number,
		final_size: PropTypes.number,
		priority: PropTypes.number,
		original_tag: PropTypes.string,
		original_tag_abbr: PropTypes.string,
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
