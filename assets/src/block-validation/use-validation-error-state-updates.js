/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { AMP_VALIDITY_REST_FIELD_NAME } from './constants';
import { BLOCK_VALIDATION_STORE_KEY } from './store';

/**
 * Custom hook managing state updates through effect hooks.
 *
 * Handling state through a context provider might be preferable in other circumstances, but in this case
 * using a store is necessary because React context is not passed down over slotfills, and we need multiple
 * components within multiple slotfills to have access to the same state.
 */
export function useValidationErrorStateUpdates() {
	const [ trackedValidationErrorsFromPost, setTrackedValidationErrorsFromPost ] = useState( [] );

	const { setValidationErrors } = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const { blockOrder, currentPost, getBlock, validationErrorsFromPost } = useSelect( ( select ) => ( {
		blockOrder: select( 'core/block-editor' ).getClientIdsWithDescendants(),
		currentPost: select( 'core/editor' ).getCurrentPost(),
		getBlock: select( 'core/block-editor' ).getBlock,
		validationErrorsFromPost: select( 'core/editor' ).getEditedPostAttribute( AMP_VALIDITY_REST_FIELD_NAME )?.results || [],
	} ) );

	/**
	 * Runs an equality check when validation errors are received before running the heavier effect.
	 */
	useEffect( () => {
		if ( ! isEqual( trackedValidationErrorsFromPost, validationErrorsFromPost ) ) {
			setTrackedValidationErrorsFromPost( validationErrorsFromPost );
		}
	}, [ trackedValidationErrorsFromPost, validationErrorsFromPost ] );

	/**
	 * Adds clientIds to the validation errors that are associated with blocks.
	 */
	useEffect( () => {
		const newValidationErrors = trackedValidationErrorsFromPost.map( ( validationError ) => {
			if ( ! validationError.error.sources ) {
				return validationError;
			}

			// Handle case that was encountered where `sources` was an object (with numeric keys).
			if ( ! Array.isArray( validationError.error.sources ) ) {
				validationError.error.sources = Object.values( validationError.error.sources );
			}

			for ( const source of validationError.error.sources ) {
				if ( ! source.block_name || undefined === source.block_content_index ) {
					continue;
				}

				if ( currentPost.id !== source.post_id ) {
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

				return { ...validationError, clientId };
			}

			return validationError;
		} );

		setValidationErrors( newValidationErrors );
	}, [ blockOrder, currentPost.id, getBlock, setValidationErrors, trackedValidationErrorsFromPost ] );

	return null;
}
