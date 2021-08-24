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
 * To render plugins information on site support page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.data Plugins data.
 * @return {JSX.Element|null} HTML markup for plugins data.
 */
export function Plugins( { data: plugins } ) {
	if ( 'object' !== typeof plugins ) {
		return null;
	}

	plugins = Object.values( plugins );

	return (
		<details open={ false }>
			<summary>
				{ __( 'Plugins', 'amp' ) }
				{ ` (${ plugins.length || 0 })` }
			</summary>
			<div className="detail-body">
				<ListItems
					className="list-items--list-style-disc"
					items={ plugins.map( ( item ) => {
						return { value: `${ item.name } ${ item.version ? '(' + item.version + ')' : '' }` };
					} ) }
				/>
			</div>
		</details>
	);
}

Plugins.propTypes = {
	data: PropTypes.array.isRequired,
};

