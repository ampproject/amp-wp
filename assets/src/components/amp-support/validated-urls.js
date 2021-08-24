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
 * To render validated urls information on site support page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.data Error data.
 * @return {JSX.Element|null} HTML markup for validated urls data.
 */
export function ValidatedUrls( { data: validatedUrls } ) {
	if ( 'object' !== typeof validatedUrls ) {
		return null;
	}

	const urls = validatedUrls.map( ( item ) => item.url ? item.url : null );

	return (
		<details open={ false }>
			<summary>
				{ __( 'Validated URLs', 'amp' ) }
				{ ` (${ validatedUrls.length || 0 })` }
			</summary>
			<div className="detail-body">
				<ListItems
					className="list-items--list-style-disc"
					items={ urls.map( ( url ) => {
						return {
							value: (
								<a href={ url } title={ url } target="_blank" rel="noreferrer">
									{ url }
								</a>
							),
						};
					} ) }
				/>
			</div>
		</details>
	);
}

ValidatedUrls.propTypes = {
	data: PropTypes.object,
};

