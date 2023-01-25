/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Loading indicator.
 *
 * @param {Object}  props        Component props.
 * @param {boolean} props.inline Display indicator as an inline element.
 */
// @todo WIP: Updated design needed.
export function Loading({ inline = false }) {
	const Tag = inline ? 'span' : 'div';

	return (
		<Tag
			className={classnames('amp-spinner-container', {
				'amp-spinner-container--inline': inline,
			})}
		>
			<Spinner />
		</Tag>
	);
}

Loading.propTypes = {
	inline: PropTypes.bool,
};
