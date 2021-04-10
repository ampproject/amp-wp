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
import { ErrorTypeIcon } from './error-type-icon';

/**
 * Panel title component for an individual error.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.kept
 * @param {string} props.title Title string from error data.
 * @param {Object} props.error Error details.
 * @param {string} props.error.type Error type.
 */
export function ErrorPanelTitle( {
	kept,
	title,
	error: { type },
} ) {
	return (
		<div
			className="amp-error__panel-title"
			title={ kept ? __( 'This error has been kept, making this URL not AMP-compatible.', 'amp' ) : '' }
		>
			<div className="amp-error__icons">
				{ type && (
					<div className={ `amp-error__error-type-icon amp-error__error-type-icon--${ type?.replace( /_/g, '-' ) }` }>
						<ErrorTypeIcon type={ type } />
					</div>
				) }
			</div>
			<div
				className="amp-error__title"
				dangerouslySetInnerHTML={ {
					/* dangerouslySetInnerHTML reason: WordPress sometimes sends back HTML in error messages. */
					__html: title,
				} }
			/>
		</div>
	);
}
ErrorPanelTitle.propTypes = {
	kept: PropTypes.bool,
	title: PropTypes.string.isRequired,
	error: PropTypes.shape( {
		type: PropTypes.string,
	} ).isRequired,
};
