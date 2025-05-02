/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	CSS_ERROR_TYPE,
	HTML_ATTRIBUTE_ERROR_TYPE,
	HTML_ELEMENT_ERROR_TYPE,
	JS_ERROR_TYPE,
} from 'amp-block-validation';

/**
 * Internal dependencies
 */
import { JSErrorIcon, HTMLErrorIcon, CSSErrorIcon } from '../../../icons';

/**
 * Component rendering an icon representing JS, CSS, or HTML.
 *
 * @param {Object} props
 * @param {string} props.type The error type.
 */
export function ErrorTypeIcon({ type }) {
	switch (type) {
		case HTML_ATTRIBUTE_ERROR_TYPE:
		case HTML_ELEMENT_ERROR_TYPE:
			return <HTMLErrorIcon />;

		case JS_ERROR_TYPE:
			return <JSErrorIcon />;

		case CSS_ERROR_TYPE:
			return <CSSErrorIcon />;

		default:
			return null;
	}
}
ErrorTypeIcon.propTypes = {
	type: PropTypes.string.isRequired,
};
