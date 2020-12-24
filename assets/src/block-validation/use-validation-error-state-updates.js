/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from './store';

/**
 * Handles cases where a validationError's `sources` are an object (with numeric keys).
 *
 * Note: this will no longer be an issue after https://github.com/ampproject/amp-wp/commit/bbb0e495a817a56b37554dfd721170712c92d7b8
 * but is still required for validation errors stored in the database prior to that commit.
 *
 * @param {Object} validationError
 */
export function convertErrorSourcesToArray( validationError ) {
	if ( ! Array.isArray( validationError.error.sources ) ) {
		validationError.error.sources = Object.values( validationError.error.sources );
	}
}

/**
 * Attempts to associate a validation error with a block current in the editor.
 *
 * @param {Object} args
 * @param {Object} args.validationError Validation error object.
 * @param {Object} args.source A single validation error source.
 * @param {number} args.currentPostId The ID of the current post.
 * @param {string[]} args.blockOrder Block client IDs in order.
 * @param {Function} args.getBlock Store selector to get a block in the current editor by client ID.
 */
export function maybeAddClientIdToValidationError( { validationError, source, currentPostId, blockOrder, getBlock } ) {
	if ( ! source.block_name || undefined === source.block_content_index ) {
		return;
	}

	if ( currentPostId !== source.post_id ) {
		return;
	}

	// Look up the block ID by index, assuming the blocks of content in the editor are the same as blocks rendered on frontend.
	const clientId = blockOrder[ source.block_content_index ];
	if ( ! clientId ) {
		return;
	}

	// Sanity check that block exists for clientId.
	const block = getBlock( clientId );
	if ( ! block ) {
		return;
	}

	// Check the block type in case a block is dynamically added/removed via the_content filter to cause alignment error.
	if ( block.name !== source.block_name ) {
		return;
	}

	validationError.clientId = clientId;
}

/**
 * Custom hook managing state updates through effect hooks.
 *
 * Handling state through a context provider might be preferable in other circumstances, but in this case
 * using a store is necessary because React context is not passed down over slotfills, and we need multiple
 * components within multiple slotfills to have access to the same state.
 */
export function useValidationErrorStateUpdates() {
	const [ previousValidationErrors, setPreviousValidationErrors ] = useState( [] );

	const { setIsFetchingErrors, setReviewLink, setValidationErrors } = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const { blockOrder, currentPost, getBlock, isSavingPost, validationErrors } = useSelect( ( select ) => ( {
		blockOrder: select( 'core/block-editor' ).getClientIdsWithDescendants(),
		currentPost: select( 'core/editor' ).getCurrentPost(),
		getBlock: select( 'core/block-editor' ).getBlock,
		isSavingPost: select( 'core/editor' ).isSavingPost(),
		validationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors(),
	} ), [] );

	/**
	 * Fetches validation errors for the current post's URL after the editor has loaded and following
	 * subsequent saves.
	 */
	useEffect( () => {
		if ( isSavingPost ) {
			return () => undefined;
		}

		let unmounted = false;
		( async () => {
			setIsFetchingErrors( true );

			try {
				const newValidation = await apiFetch( {
					path: addQueryArgs( `/amp/v1/validate-post-url/${ currentPost.id }`, { context: 'amp-editor' } ),
				} );

				if ( unmounted ) {
					return;
				}

				setValidationErrors( newValidation.results );
				setReviewLink( newValidation.review_link );
				setIsFetchingErrors( false );
			} catch ( e ) {}
		} )();

		return () => {
			unmounted = true;
		};
	}, [ currentPost.id, isSavingPost, setIsFetchingErrors, setReviewLink, setValidationErrors ] );

	/**
	 * Runs an equality check when validation errors are received before running the heavier effect.
	 */
	useEffect( () => {
		if ( validationErrors && ! isEqual( previousValidationErrors, validationErrors ) ) {
			setPreviousValidationErrors( validationErrors );
		}
	}, [ previousValidationErrors, validationErrors ] );

	/**
	 * Adds clientIds to the validation errors that are associated with blocks.
	 */
	useEffect( () => {
		const newValidationErrors = previousValidationErrors.map( ( validationError ) => {
			if ( ! validationError.error.sources ) {
				return validationError;
			}

			convertErrorSourcesToArray( validationError );

			for ( const source of validationError.error.sources ) {
				// The loop can finish if the validation error (which is passed by reference below) has obtained a clientId.
				if ( 'clientId' in validationError ) {
					break;
				}

				maybeAddClientIdToValidationError( {
					validationError,
					source,
					getBlock,
					blockOrder,
					currentPostId: currentPost.id,
				} );
			}

			return validationError;
		} );

		setValidationErrors( newValidationErrors );
	}, [ blockOrder, currentPost.id, getBlock, setValidationErrors, previousValidationErrors ] );
}
