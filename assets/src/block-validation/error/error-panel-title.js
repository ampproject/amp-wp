/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { BlockIcon } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { VALIDATION_ERROR_ACK_REJECTED_STATUS, VALIDATION_ERROR_NEW_REJECTED_STATUS } from '../constants';
import { ErrorTypeIcon } from './error-type-icon';

/**
 * Panel title component for an individual error.
 *
 * @param {Object} props Component props.
 * @param {Object} props.blockType
 * @param {string} props.title Title string from error data.
 * @param {Object} props.error Error details.
 * @param {string} props.error.type Error type.
 * @param {number} props.status Error status.
 */
export function ErrorPanelTitle( { blockType, title, error: { type }, status } ) {
	const kept = status === VALIDATION_ERROR_ACK_REJECTED_STATUS || status === VALIDATION_ERROR_NEW_REJECTED_STATUS;

	return (
		<>
			<div className="amp-error__icons">
				<div className={ `amp-error__error-type-icon amp-error__error-type-icon--${ type.replace( /_/g, '-' ) }` }>
					<ErrorTypeIcon type={ type } />
				</div>
				{ blockType?.icon && (
					<div className="amp-error__block-type-icon">
						<BlockIcon icon={ blockType.icon } />
					</div>
				) }
			</div>
			<span className="amp-error__title-text">
				<span dangerouslySetInnerHTML={ { __html: title } } />
				{ kept && (
					<span className="amp-error-alert" title={ __( 'This error has been kept, making this URL not AMP-compatible.', 'amp' ) }>
						{ '!' }
					</span>
				) }
			</span>
		</>
	);
}
ErrorPanelTitle.propTypes = {
	blockType: PropTypes.object,
	title: PropTypes.string.isRequired,
	error: PropTypes.shape( {
		type: PropTypes.string,
	} ).isRequired,
	status: PropTypes.number,
};
