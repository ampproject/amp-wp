/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { usePrevious } from '@wordpress/compose';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from '../store';
import BellIcon from '../../../images/bell-icon.svg';
import { SidebarNotification } from '../../block-editor/components/sidebar-notification';

/**
 * AMP re-validate status message.
 */
export default function AMPRevalidateNotification() {
	const [ didFetchErrors, setDidFetchErrors ] = useState( false );
	const [ loadingMessage, setLoadingMessage ] = useState( '' );
	const { autosave, savePost } = useDispatch( 'core/editor' );

	const {
		hasErrorsFromRemovedBlocks,
		hasActiveMetaboxes,
		isDraft,
		isFetchingErrors,
		isEditedPostNew,
		isPostDirty,
	} = useSelect( ( select ) => {
		let _hasErrorsFromRemovedBlocks = false;
		for ( const validationError of select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ) {
			const { clientId } = validationError;
			const blockDetails = clientId ? select( 'core/block-editor' ).getBlock( clientId ) : null;
			if ( clientId && ! blockDetails ) {
				_hasErrorsFromRemovedBlocks = true;
				break;
			}
		}

		return {
			hasErrorsFromRemovedBlocks: _hasErrorsFromRemovedBlocks,
			hasActiveMetaboxes: select( 'core/edit-post' ).hasMetaBoxes(),
			isDraft: [ 'draft', 'auto-draft' ].indexOf( select( 'core/editor' ).getEditedPostAttribute( 'status' ) ) !== -1,
			isEditedPostNew: select( 'core/editor' ).isEditedPostNew(),
			isFetchingErrors: select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors(),
			isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
		};
	}, [] );

	const wasEditedPostNew = usePrevious( isEditedPostNew );
	const wasFetchingErrors = usePrevious( isFetchingErrors );

	useEffect( () => {
		if ( didFetchErrors ) {
			return;
		}

		// Set up the state right after errors fetching has finished.
		if ( ! isFetchingErrors && wasFetchingErrors ) {
			setDidFetchErrors( true );
		}
	}, [ didFetchErrors, isFetchingErrors, wasFetchingErrors ] );

	/**
	 * Display best-suited loading message depending if the post has been
	 * already validated or not, or the editor has just been opened.
	 */
	useEffect( () => {
		if ( didFetchErrors ) {
			setLoadingMessage( __( 'Re-validating page content.', 'amp' ) );
		} else if ( isEditedPostNew || wasEditedPostNew ) {
			setLoadingMessage( __( 'Validating page content.', 'amp' ) );
		} else {
			setLoadingMessage( __( 'Loading…', 'amp' ) );
		}
	}, [ didFetchErrors, isEditedPostNew, wasEditedPostNew ] );

	if ( isFetchingErrors ) {
		return (
			<SidebarNotification message={ loadingMessage } isLoading={ true } />
		);
	}

	/**
	 * For posts where meta boxes are present, it's impossible to tell
	 * if a meta box content has changed or not. Because of that, a dirty
	 * state is ignored and it's always possible to save a post.
	 * Likewise, we always display the re-validate message if there are
	 * active meta boxes. Also show a re-validate message if there are validation
	 * errors which used to be in the content but are no longer found, potentially
	 * due to switching from the visual editor to the code editor.
	 */
	if ( ! isPostDirty && ! hasActiveMetaboxes && ! hasErrorsFromRemovedBlocks ) {
		return null;
	}

	return (
		<SidebarNotification
			icon={ <BellIcon /> }
			message={ hasActiveMetaboxes || ! isPostDirty
				? __( 'Page content may have changed.', 'amp' )
				: __( 'Page content has changed.', 'amp' ) }
			action={ isDraft ? (
				<Button
					isLink
					onClick={ () => savePost( { isPreview: true } ) }>
					{ __( 'Save draft and validate', 'amp' ) }
				</Button>
			) : (
				<Button
					isLink
					onClick={ () => autosave( { isPreview: true } ) }>
					{ __( 'Re-validate', 'amp' ) }
				</Button>
			) }
		/>
	);
}
