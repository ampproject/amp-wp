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
 * To render validated urls information on site support page.
 *
 * @param {Object} props               Component props.
 * @param {Array}  props.validatedUrls Error data.
 * @return {JSX.Element|null} HTML markup for validated urls data.
 */
export function ValidatedUrls( { validatedUrls } ) {
	if ( ! Array.isArray( validatedUrls ) ) {
		return null;
	}

	const urls = validatedUrls.map( ( item ) => item.url ? item.url : null );
	const items = urls.map( ( url ) => {
		return {
			value: (
				<a href={ url } title={ url } target="_blank" rel="noreferrer">
					{ url }
				</a>
			),
		};
	} );

	return (
		<details open={ false }>
			<summary>
				{
					sprintf(
						/* translators: Placeholder is the number of validated URLs. */
						__( 'Validated URLs (%d)', 'amp' ),
						validatedUrls.length,
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

ValidatedUrls.propTypes = {
	validatedUrls: PropTypes.array.isRequired,
};

