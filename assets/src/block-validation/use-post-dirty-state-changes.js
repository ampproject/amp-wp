/**
 * WordPress dependencies
 */
import { useDebounce } from '@wordpress/compose';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { subscribe, useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from './store';

const DELAY_MS = 500;

export function usePostDirtyStateChanges() {
	const [ content, setContent ] = useState();
	const [ updatedContent, setUpdatedContent ] = useState();
	const subscription = useRef( null );
	const { setIsPostDirty } = useDispatch( BLOCK_VALIDATION_STORE_KEY );
	const {
		getEditedPostContent,
		isPostDirty,
		isSavingOrPreviewingPost,
	} = useSelect( ( select ) => ( {
		getEditedPostContent: select( 'core/editor' ).getEditedPostContent,
		isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
		isSavingOrPreviewingPost:
			( select( 'core/editor' ).isSavingPost() && ! select( 'core/editor' ).isAutosavingPost() ) ||
			select( 'core/editor' ).isPreviewingPost(),
	} ), [] );

	/**
	 * Post is no longer in a dirty state after save.
	 *
	 * We're using a separate effect for resetting the flag since the listener
	 * gets unsubscribed from the store changes whenever post gets into a dirty
	 * state.
	 *
	 * The following effect (indirectly) resubscribes the listener once the post
	 * is no longer in a dirty state.
	 */
	useEffect( () => {
		if ( isPostDirty && isSavingOrPreviewingPost ) {
			setIsPostDirty( false );
		}
	}, [ isPostDirty, isSavingOrPreviewingPost, setIsPostDirty ] );

	/**
	 * Whenever a fresh post content differs from the one that is stored in the
	 * state, it's safe to assume that the post is in a dirty state.
	 */
	useEffect( () => {
		if ( ! content ) {
			setContent( updatedContent );
			return;
		}

		if ( updatedContent !== content ) {
			setIsPostDirty( true );
			setContent( updatedContent );
		}
	}, [ content, setIsPostDirty, updatedContent ] );

	/**
	 * Keep internal content state in sync with editor state.
	 */
	const listener = useCallback( () => {
		setUpdatedContent( getEditedPostContent() );
	}, [ getEditedPostContent ] );

	/**
	 * Debounce calls to the store listener for performance reasons.
	 */
	const debouncedListener = useDebounce( listener, DELAY_MS );

	/**
	 * Only subscribe to the store changes if the post is not in a dirty state.
	 */
	useEffect( () => {
		if ( isPostDirty && subscription.current ) {
			subscription.current();
			subscription.current = null;
		} else if ( ! isSavingOrPreviewingPost && ! isPostDirty && ! subscription.current ) {
			subscription.current = subscribe( debouncedListener );
		}

		return () => {
			if ( subscription.current ) {
				subscription.current();
				subscription.current = null;
			}
		};
	}, [ debouncedListener, isPostDirty, isSavingOrPreviewingPost ] );
}
