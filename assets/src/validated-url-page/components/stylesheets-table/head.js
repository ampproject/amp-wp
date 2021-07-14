/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

export default function StylesheetsTableHead() {
	return (
		<thead>
			<tr>
				<td className="column-stylesheet_expand">
					<span className="screen-reader-text">
						{ __( 'Expand/collapse', 'amp' ) }
					</span>
				</td>
				<td className="column-stylesheet_order">
					{ __( 'Order', 'amp' ) }
				</td>
				<td className="column-original_size">
					{ __( 'Original', 'amp' ) }
				</td>
				<td className="column-minified">
					{ __( 'Minified', 'amp' ) }
				</td>
				<td className="column-final_size">
					{ __( 'Final', 'amp' ) }
				</td>
				<td
					className="column-percentage"
					title={ __( 'Stylesheet bytes of total CSS added to page', 'amp' ) }
				>
					{ __( 'Percent', 'amp' ) }
				</td>
				<td className="column-priority">
					{ __( 'Priority', 'amp' ) }
				</td>
				<td className="column-stylesheet_included">
					{ __( 'Included', 'amp' ) }
				</td>
				<td className="column-markup">
					{ __( 'Markup', 'amp' ) }
				</td>
				<td className="column-sources_with_invalid_output">
					{ __( 'Sources', 'amp' ) }
				</td>
			</tr>
		</thead>
	);
}
