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
	if ( ! Array.isArray( themes ) ) {
		return null;
	}

	const items = themes.map( ( item ) => {
		return { value: `${ item.name } ${ item.version ? '(' + item.version + ')' : '' }` };
	} );

	return (
		<details open={ false }>
			<summary>
				{ __( 'Theme', 'amp' ) }
			</summary>
			<div className="detail-body">
				<ListItems
					disc={ true }
					items={ items }
				/>
			</div>
		</details>
	);
}

Theme.propTypes = {
	data: PropTypes.array.isRequired,
};
