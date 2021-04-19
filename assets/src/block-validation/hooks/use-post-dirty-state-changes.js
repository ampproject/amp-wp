/**
 * WordPress dependencies
 */
import { useDebounce } from '@wordpress/compose';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { subscribe, useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from '../store';

const DELAY_MS = 500;

export function usePostDirtyStateChanges() {
	const [ content, setContent ] = useState( null );
	const [ updatedContent, setUpdatedContent ] = useState();
	const subscription = useRef( null );

	const {
		setIsPostDirty,
		setMaybeIsPostDirty,
	} = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const {
		getEditedPostContent,
		hasErrorsFromRemovedBlocks,
		hasActiveMetaboxes,
		isPostDirty,
		isSavingOrPreviewingPost,
	} = useSelect( ( select ) => ( {
		getEditedPostContent: select( 'core/editor' ).getEditedPostContent,
		hasErrorsFromRemovedBlocks: Boolean( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors().find(
			( { clientId } ) => clientId && ! select( 'core/block-editor' ).getBlockName( clientId ) ),
		),
		hasActiveMetaboxes: select( 'core/edit-post' ).hasMetaBoxes(),
		isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
		isSavingOrPreviewingPost:
			( select( 'core/editor' ).isSavingPost() && ! select( 'core/editor' ).isAutosavingPost() ) ||
			select( 'core/editor' ).isPreviewingPost(),
	} ), [] );

	/**
	 * Remove subscription when component is unmounted.
	 */
	useEffect( () => () => {
		if ( subscription.current ) {
			subscription.current();
		}
	}, [] );

	/**
	 * Post is no longer in a dirty state after save.
	 *
	 * We're using a separate effect for resetting the flag since the listener
	 * gets unsubscribed from the store changes whenever post gets into a dirty
	 * state.
	 */
	useEffect( () => {
		if ( isPostDirty && isSavingOrPreviewingPost ) {
			setIsPostDirty( false );
			setContent( null );
		}
	}, [ isPostDirty, isSavingOrPreviewingPost, setIsPostDirty ] );

	/**
	 * Whenever a fresh post content differs from the one that is stored in the
	 * state, it's safe to assume that the post is in a dirty state.
	 *
	 * When the content is null, we're resetting both the `content` and the
	 * `updatedContent`.
	 */
	useEffect( () => {
		if ( content === null ) {
			const initialContent = getEditedPostContent();

			setContent( initialContent );
			setUpdatedContent( initialContent );

			return;
		}

		if ( updatedContent !== content ) {
			setIsPostDirty( true );
		}
	}, [ content, getEditedPostContent, setIsPostDirty, updatedContent ] );

	/**
	 * Post may have changed.
	 *
	 * In some cases it's hard to tell if a post content has changed or not.
	 * Examples are meta box content changes or presence of validation errors
	 * which used to be in the content but are no longer found, potentially
	 * due to switching from the visual editor to the code editor.
	 */
	useEffect( () => {
		setMaybeIsPostDirty( ! isPostDirty && ( hasActiveMetaboxes || hasErrorsFromRemovedBlocks ) );
	}, [ hasActiveMetaboxes, hasErrorsFromRemovedBlocks, isPostDirty, setMaybeIsPostDirty ] );

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
	}, [ debouncedListener, isPostDirty, isSavingOrPreviewingPost ] );
}
