/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
	AMP_VALIDATION_ERROR_NOTICE_ID,
	AMP_VALIDITY_REST_FIELD_NAME,
} from '../constants';

export const removeValidationErrorNotice = () => {
	const { getNotices } = select( 'core/notices' );
	const { removeNotice } = dispatch( 'core/notices' );

	if ( getNotices().filter( ( { id } ) => id === AMP_VALIDATION_ERROR_NOTICE_ID ) ) {
		removeNotice( AMP_VALIDATION_ERROR_NOTICE_ID );
	}
};

let previousValidationErrors = [];

export const maybeResetValidationErrors = () => {
	const { getValidationErrors } = select( 'amp/block-validation' );
	const { resetValidationErrors } = dispatch( 'amp/block-validation' );

	if ( getValidationErrors().length > 0 ) {
		resetValidationErrors();
		removeValidationErrorNotice();
		previousValidationErrors = [];
	}
};

/**
 * Update blocks' validation errors in the store.
 */
export const updateValidationErrors = () => {
	const { getClientIdsWithDescendants, getBlock } = select( 'core/block-editor' );
	const { getCurrentPost } = select( 'core/editor' );
	const { resetValidationErrors, addValidationError, updateReviewLink } = dispatch( 'amp/block-validation' );

	const blockOrder = getClientIdsWithDescendants();

	if ( blockOrder.length === 0 ) {
		return;
	}

	const currentPost = getCurrentPost();

	const ampValidity = currentPost[ AMP_VALIDITY_REST_FIELD_NAME ] || {};

	if ( ! ampValidity.results || ! ampValidity.review_link ) {
		return;
	}

	const validationErrors = ampValidity.results.filter( ( result ) => {
		return result.term_status !== VALIDATION_ERROR_ACK_ACCEPTED_STATUS; // If not accepted by the user.
	} ).map( ( { error } ) => error );

	if ( isEqual( validationErrors, previousValidationErrors ) ) {
		return;
	}

	previousValidationErrors = validationErrors;
	resetValidationErrors();

	if ( 0 === validationErrors.length ) {
		removeValidationErrorNotice();

		return;
	}

	updateReviewLink( ampValidity.review_link );

	validationErrorLoop:
	for ( const validationError of validationErrors ) {
		if ( ! validationError.sources ) {
			addValidationError( validationError );

			break;
		}

		for ( const source of validationError.sources ) {
			// Skip sources that are not for blocks.
			if ( ! source.block_name || undefined === source.block_content_index || currentPost.id !== source.post_id ) {
				continue;
			}

			// Look up the block ID by index, assuming the blocks of content in the editor are the same as blocks rendered on frontend.
			const clientId = blockOrder[ source.block_content_index ];

			if ( ! clientId ) {
				continue;
			}

			// Sanity check that block exists for clientId.
			const block = getBlock( clientId );
			if ( ! block ) {
				continue;
			}

			// Check the block type in case a block is dynamically added/removed via the_content filter to cause alignment error.
			if ( block.name !== source.block_name ) {
				continue;
			}

			addValidationError( validationError, clientId );

			// Stop looking for sources, since we aren't looking for parent blocks.
			continue validationErrorLoop;
		}

		addValidationError( validationError );
	}

	maybeDisplayNotice();
};

/**
 * Handle state change regarding validation errors.
 *
 * This is essentially a JS implementation of \AMP_Validation_Manager::print_edit_form_validation_status() in PHP.
 *
 * @return {void}
 */
export const maybeDisplayNotice = () => {
	const { getValidationErrors, isSanitizationAutoAccepted, getReviewLink } = select( 'amp/block-validation' );
	const { createWarningNotice } = dispatch( 'core/notices' );

	const validationErrors = getValidationErrors();
	const validationErrorCount = validationErrors.length;

	let noticeMessage;

	noticeMessage = sprintf(
		/* translators: %s: number of issues */
		_n(
			'There is %s issue from AMP validation which needs review.',
			'There are %s issues from AMP validation which need review.',
			validationErrorCount,
			'amp'
		),
		validationErrorCount
	);

	const blockValidationErrors = validationErrors.filter( ( { clientId } ) => clientId );
	const blockValidationErrorCount = blockValidationErrors.length;

	if ( blockValidationErrorCount > 0 ) {
		noticeMessage += ' ' + sprintf(
			/* translators: %s: number of block errors. */
			_n(
				'%s issue is directly due to content here.',
				'%s issues are directly due to content here.',
				blockValidationErrorCount,
				'amp'
			),
			blockValidationErrorCount
		);
	} else if ( validationErrors.length === 1 ) {
		noticeMessage += ' ' + __( 'The issue is not directly due to content here.', 'amp' );
	} else {
		noticeMessage += ' ' + __( 'The issues are not directly due to content here.', 'amp' );
	}

	noticeMessage += ' ';

	if ( isSanitizationAutoAccepted() ) {
		const rejectedBlockValidationErrors = blockValidationErrors.filter( ( error ) => {
			return (
				VALIDATION_ERROR_NEW_REJECTED_STATUS === error.status ||
				VALIDATION_ERROR_ACK_REJECTED_STATUS === error.status
			);
		} );

		const rejectedValidationErrors = validationErrors.filter( ( error ) => {
			return (
				VALIDATION_ERROR_NEW_REJECTED_STATUS === error.status ||
				VALIDATION_ERROR_ACK_REJECTED_STATUS === error.status
			);
		} );

		const totalRejectedErrorsCount = rejectedBlockValidationErrors.length + rejectedValidationErrors.length;

		if ( totalRejectedErrorsCount === 0 ) {
			noticeMessage += __( 'However, your site is configured to automatically accept sanitization of the offending markup.', 'amp' );
		} else {
			noticeMessage += _n(
				'Your site is configured to automatically accept sanitization errors, but this error could be from when auto-acceptance was not selected, or from manually rejecting an error.',
				'Your site is configured to automatically accept sanitization errors, but these errors could be from when auto-acceptance was not selected, or from manually rejecting an error.',
				validationErrors.length,
				'amp'
			);
		}
	} else {
		noticeMessage += __( 'Non-accepted validation errors prevent AMP from being served, and the user will be redirected to the non-AMP version.', 'amp' );
	}

	const options = {
		id: AMP_VALIDATION_ERROR_NOTICE_ID,
	};

	const reviewLink = getReviewLink();

	if ( reviewLink ) {
		options.actions = [
			{
				label: __( 'Review issues', 'amp' ),
				url: reviewLink,
			},
		];
	}

	createWarningNotice( noticeMessage, options );
};
