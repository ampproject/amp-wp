/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ListItems } from '../list-items';

/**
 * To render plugins information on site support page.
 *
 * @param {Object} props         Component props.
 * @param {Array}  props.plugins Plugins data.
 * @return {JSX.Element|null} HTML markup for plugins data.
 */
export function Plugins( { plugins } ) {
	if ( ! Array.isArray( plugins ) ) {
		return null;
	}

	const items = plugins.map( ( item ) => {
		return { value: `${ item.name } ${ item.version ? '(' + item.version + ')' : '' }` };
	} );

	return (
		<details open={ false }>
			<summary>
				{
					sprintf(
						/* translators: Placeholder is the number of plugins */
						__( 'Plugins (%s)', 'amp' ),
						plugins.length,
					)
				}
			</summary>
			<div className="detail-body">
				<ListItems
					isDisc={ true }
					items={ items }
				/>
			</div>
		</details>
	);
}

Plugins.propTypes = {
	plugins: PropTypes.array.isRequired,
};

