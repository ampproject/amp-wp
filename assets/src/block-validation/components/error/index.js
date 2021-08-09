/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
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
import './style.css';
import { BLOCK_VALIDATION_STORE_KEY } from '../../store';
import { ErrorPanelTitle } from './error-panel-title';
import { ErrorContent } from './error-content';

/**
 * Component rendering an individual error. Parent component is a <ul>.
 *
 * @param {Object} args          Component props.
 * @param {string} args.clientId
 * @param {number} args.error
 * @param {number} args.status
 * @param {number} args.term_id
 * @param {number} args.title
 */
export function Error( { clientId, error, status, term_id: termId, title } ) {
	const { selectBlock } = useDispatch( 'core/block-editor' );
	const reviewLink = useSelect( ( select ) => select( BLOCK_VALIDATION_STORE_KEY ).getReviewLink(), [] );
	const reviewed = status === VALIDATION_ERROR_ACK_ACCEPTED_STATUS || status === VALIDATION_ERROR_ACK_REJECTED_STATUS;
	const kept = status === VALIDATION_ERROR_ACK_REJECTED_STATUS || status === VALIDATION_ERROR_NEW_REJECTED_STATUS;
	const external = ! Boolean( clientId );

	const { blockType, removed } = useSelect( ( select ) => {
		const blockName = select( 'core/block-editor' ).getBlockName( clientId );

		return {
			removed: clientId && ! blockName,
			blockType: select( 'core/blocks' ).getBlockType( blockName ),
		};
	}, [ clientId ] );

	let detailsUrl = null;
	if ( reviewLink ) {
		detailsUrl = new URL( reviewLink );
		detailsUrl.hash = `#tag-${ termId }`;
	}

	const panelClassNames = classnames( 'amp-error', {
		'amp-error--reviewed': reviewed,
		'amp-error--new': ! reviewed,
		'amp-error--removed': removed,
		'amp-error--kept': kept,
		[ `error-${ clientId }` ]: clientId,
	} );

	return (
		<PanelBody
			className={ panelClassNames }
			title={
				<ErrorPanelTitle
					error={ error }
					kept={ kept }
					title={ title }
				/>
			}
			initialOpen={ false }
		>
			<ErrorContent
				blockType={ blockType }
				clientId={ clientId }
				error={ error }
				external={ external }
				removed={ removed }
				status={ status }
			/>

			<div className="amp-error__actions">
				{ ! ( removed || external ) && (
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
				{ detailsUrl && (
					<ExternalLink
						href={ detailsUrl.href }
						className="amp-error__details-link"
					>
						{ __( 'View details', 'amp' ) }
					</ExternalLink>
				) }
			</div>

		</PanelBody>
	);
}
Error.propTypes = {
	clientId: PropTypes.string,
	error: PropTypes.shape( {
		sources: PropTypes.arrayOf( PropTypes.object ).isRequired,
		type: PropTypes.string,
	} ).isRequired,
	status: PropTypes.number.isRequired,
	term_id: PropTypes.number.isRequired,
	title: PropTypes.string.isRequired,
};
