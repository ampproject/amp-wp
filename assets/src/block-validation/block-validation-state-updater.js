/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { AMP_VALIDITY_REST_FIELD_NAME } from './constants';
import { BLOCK_VALIDATION_STORE_KEY } from './store';

/**
 * Non-display component managing state updates through effect hooks.
 *
 * Handling state through a context provider might be preferable in other circumstances, but in this case
 * using a store is necessary because React context is not passed down over slotfills, and we need multiple
 * components within multiple slotfills to have access to the same state.
 */
export function BlockValidationStateUpdater() {
	const { rawValidationErrors, validationErrors } = useSelect( ( select ) => ( {
		rawValidationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getRawValidationErrors(),
		validationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors(),
	} ) );

	const { setRawValidationErrors, setReviewLink, setValidationErrors } = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const {
		blockCount,
		getBlock,
		getClientIdsWithDescendants,
		getCurrentPost,
		reviewLinkFromSource,
		rawValidationErrorsFromSource,
	} = useSelect( ( select ) => (
		{
			blockCount: select( 'core/block-editor' ).getBlockCount(),
			getBlock: select( 'core/block-editor' ).getBlock,
			getClientIdsWithDescendants: select( 'core/block-editor' ).getClientIdsWithDescendants,
			getCurrentPost: select( 'core/editor' ).getCurrentPost,
			// eslint-disable-next-line camelcase
			reviewLinkFromSource: select( 'core/editor' ).getEditedPostAttribute( AMP_VALIDITY_REST_FIELD_NAME )?.review_link || null,
			rawValidationErrorsFromSource: select( 'core/editor' ).getEditedPostAttribute( AMP_VALIDITY_REST_FIELD_NAME )?.results || [],
		}
	) );

	/**
	 * Adds the review link to state when it changes in the source data.
	 */
	useEffect( () => {
		setReviewLink( reviewLinkFromSource );
	}, [ reviewLinkFromSource, setReviewLink ] );

	/**
	 * Updates block validation errors with client IDs from editor state.
	 */
	useEffect( () => {
		if ( 0 === blockCount ) {
			return;
		}

		if ( isEqual( rawValidationErrorsFromSource, rawValidationErrors ) ) {
			return;
		}

		const blockOrder = getClientIdsWithDescendants();

		const newValidationErrors = rawValidationErrorsFromSource.map( ( validationError ) => {
			if ( ! validationError.error.sources ) {
				return { ...validationError, ...validationError.error };
			}

			// Handle case that was encountered where `sources` was an object (with numeric keys).
			if ( ! Array.isArray( validationError.error.sources ) ) {
				validationError.error.sources = Object.values( validationError.error.sources );
			}

			/**
			 * @param {Object} source                     Error source information.
			 * @param {string} source.block_name          Name of the block associated with the error.
			 * @param {number} source.block_content_index The block's index in the list of blocks.
			 * @param {number} source.post_id             ID of the post associated with the error.
			 */
			for ( const source of validationError.error.sources ) {
				// Skip sources that are not for blocks.
				if ( ! source.block_name || undefined === source.block_content_index || getCurrentPost().id !== source.post_id ) {
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

				return { ...validationError, ...validationError.error, clientId };
			}

			return { ...validationError, ...validationError.error };
		} );

		setRawValidationErrors( rawValidationErrorsFromSource );
		setValidationErrors( newValidationErrors );
	}, [
		blockCount,
		getBlock,
		getClientIdsWithDescendants,
		getCurrentPost,
		rawValidationErrors,
		setRawValidationErrors,
		setValidationErrors,
		validationErrors,
		rawValidationErrorsFromSource,
	] );

	return null;
}
