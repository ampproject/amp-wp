/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
} from 'amp-block-validation';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { PanelBody, Button, ExternalLink } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from '../store';
import { ErrorPanelTitle } from './error-panel-title';
import { ErrorContent } from './error-content';

/**
 * Component rendering an individual error. Parent component is a <ul>.
 *
 * @param {Object} args Component props.
 * @param {string} args.clientId
 * @param {number} args.status
 * @param {number} args.term_id
 */
export function Error( { clientId, status, term_id: termId, ...props } ) {
	const { selectBlock } = useDispatch( 'core/block-editor' );
	const reviewLink = useSelect( ( select ) => select( BLOCK_VALIDATION_STORE_KEY ).getReviewLink() );
	const reviewed = status === VALIDATION_ERROR_ACK_ACCEPTED_STATUS || status === VALIDATION_ERROR_ACK_REJECTED_STATUS;

	const { blockType } = useSelect( ( select ) => {
		const blockDetails = clientId ? select( 'core/block-editor' ).getBlock( clientId ) : null;
		const blockTypeDetails = blockDetails ? select( 'core/blocks' ).getBlockType( blockDetails.name ) : null;

		return {
			blockType: blockTypeDetails,
		};
	}, [ clientId ] );

	let detailsUrl = null;
	if ( reviewLink ) {
		detailsUrl = new URL( reviewLink );
		detailsUrl.hash = `#tag-${ termId }`;
	}

	return (
		<li className="amp-error-container">
			<PanelBody
				className={ `amp-error amp-error--${ reviewed ? 'reviewed' : 'new' }${ clientId ? ` error-${ clientId }` : '' }` }
				title={
					<ErrorPanelTitle { ...props } blockType={ blockType } status={ status } />
				}
				initialOpen={ false }
			>
				<ErrorContent { ...props } clientId={ clientId } blockType={ blockType } status={ status } />

				<div className="amp-error__actions">
					{ clientId && (
						<Button
							className="amp-error__select-block"
							isSecondary
							onClick={ () => {
								selectBlock( clientId );
							} }
						>
							{ __( 'Select block', 'amp' ) }
						</Button>
					) }
					<ExternalLink
						href={ detailsUrl.href }
						className="amp-error__details-link"
					>
						{ __( 'View details', 'amp' ) }
					</ExternalLink>
				</div>

			</PanelBody>
		</li>
	);
}
Error.propTypes = {
	clientId: PropTypes.string,
	status: PropTypes.number.isRequired,
	term_id: PropTypes.number.isRequired,
};
