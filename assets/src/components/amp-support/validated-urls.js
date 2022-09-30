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
 * Render validated urls information on site support page.
 *
 * @param {Object} props               Component props.
 * @param {Array}  props.validatedUrls List of validated URLs.
 * @return {JSX.Element|null} HTML markup for validated urls data.
 */
export function ValidatedUrls({ validatedUrls }) {
	if (!Array.isArray(validatedUrls)) {
		return null;
	}

	const items = validatedUrls.map((item) => ({
		url: item.url ?? null,
	}));

	return (
		<details open={false}>
			<summary>
				{sprintf(
					/* translators: Placeholder is the number of validated URLs. */
					__('Validated URLs (%d)', 'amp'),
					validatedUrls.length
				)}
			</summary>
			<div className="detail-body">
				<ListItems isDisc={true} items={items} />
			</div>
		</details>
	);
}

ValidatedUrls.propTypes = {
	validatedUrls: PropTypes.array.isRequired,
};
