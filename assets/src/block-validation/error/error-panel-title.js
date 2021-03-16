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
import { ErrorTypeIcon } from './error-type-icon';

/**
 * Panel title component for an individual error.
 *
 * @param {Object} props Component props.
 * @param {Object} props.blockType
 * @param {boolean} props.kept
 * @param {string} props.title Title string from error data.
 * @param {Object} props.error Error details.
 * @param {string} props.error.type Error type.
 */
export function ErrorPanelTitle( {
	blockType,
	kept,
	title,
	error: { type },
} ) {
	const [ titleText ] = title.split( ':' );

	return (
		<>
			<div className="amp-error__icons">
				{ type && (
					<div className={ `amp-error__error-type-icon amp-error__error-type-icon--${ type?.replace( /_/g, '-' ) }` }>
						<ErrorTypeIcon type={ type } />
					</div>
				) }
				{ blockType?.icon && (
					<div className="amp-error__block-type-icon">
						<BlockIcon icon={ blockType.icon } />
					</div>
				) }
			</div>
			<div
				className="amp-error__title"
				title={ kept ? __( 'This error has been kept, making this URL not AMP-compatible.', 'amp' ) : '' }
			>
				<div className="amp-error__title-text">
					{ titleText }
				</div>
			</div>
		</>
	);
}
ErrorPanelTitle.propTypes = {
	blockType: PropTypes.object,
	kept: PropTypes.bool,
	title: PropTypes.string.isRequired,
	error: PropTypes.shape( {
		type: PropTypes.string,
	} ).isRequired,
};
