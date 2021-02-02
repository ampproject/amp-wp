/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from './store';

export function usePostDirtinessChanges() {
	const [ content, setContent ] = useState();
	const [ previousContent, setPreviousContent ] = useState();
	const [ shouldUpdatePreviousContent, setShouldUpdatePreviousContent ] = useState( true );

	const { setIsPostDirty } = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const {
		blocks,
		getEditedPostContent,
		isPostDirty,
		isSavingPost,
	} = useSelect( ( select ) => ( {
		blocks: select( 'core/editor' ).getBlocks(),
		getEditedPostContent: select( 'core/editor' ).getEditedPostContent,
		isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
		isSavingPost: select( 'core/editor' ).isSavingPost(),
	} ), [] );

	// Getting content is expensive, so we update it only if blocks list
	// or the dirtiness state change.
	useEffect( () => {
		if ( ! isPostDirty ) {
			setContent( getEditedPostContent() );
		}
	}, [ blocks, getEditedPostContent, isPostDirty ] );

	useEffect( () => {
		if ( isSavingPost ) {
			if ( isPostDirty ) {
				setShouldUpdatePreviousContent( true );
				setIsPostDirty( false );
			}

			return;
		}

		if ( shouldUpdatePreviousContent ) {
			setPreviousContent( getEditedPostContent() );
			setShouldUpdatePreviousContent( false );
			return;
		}

		if ( isPostDirty ) {
			return;
		}

		// Post is not considered dirty if there is no content or the content
		// didn't change.
		if ( ( ! previousContent && ! content ) || ( previousContent === content ) ) {
			return;
		}

		setIsPostDirty( true );
	}, [ content, getEditedPostContent, isPostDirty, isSavingPost, previousContent, setIsPostDirty, shouldUpdatePreviousContent ] );
}
