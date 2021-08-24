/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ListItems } from '../list-items';

/**
 * To render theme information on site support page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.data Theme data.
 * @return {JSX.Element|null} HTML markup for theme data.
 */
export function Theme( { data: themes } ) {
	if ( 'object' !== typeof themes ) {
		return null;
	}

	return (
		<details open={ false }>
			<summary>
				{ __( 'Theme', 'amp' ) }
			</summary>
			<div className="detail-body">
				<ListItems
					className="list-items--list-style-disc"
					items={ themes.map( ( item ) => {
						return { value: `${ item.name } ${ item.version ? '(' + item.version + ')' : '' }` };
					} ) }
				/>
			</div>
		</details>
	);
}

Theme.propTypes = {
	data: PropTypes.object,
};
