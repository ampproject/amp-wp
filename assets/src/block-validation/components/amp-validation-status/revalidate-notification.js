/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from '../../store';
import BellIcon from '../../../../images/bell-icon.svg';
import { SidebarNotification } from '../sidebar-notification';
import { useErrorsFetchingStateChanges } from '../../hooks/use-errors-fetching-state-changes';

/**
 * AMP re-validate status message.
 */
export default function AMPRevalidateNotification() {
	const { autosave, savePost } = useDispatch( 'core/editor' );
	const { isFetchingErrors, fetchingErrorsMessage } = useErrorsFetchingStateChanges();

	const {
		hasErrorsFromRemovedBlocks,
		hasActiveMetaboxes,
		isDraft,
		isPostDirty,
	} = useSelect( ( select ) => ( {
		hasErrorsFromRemovedBlocks: Boolean( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors().find(
			( { clientId } ) => clientId && ! select( 'core/block-editor' ).getBlockName( clientId ) ),
		),
		hasActiveMetaboxes: select( 'core/edit-post' ).hasMetaBoxes(),
		isDraft: [ 'draft', 'auto-draft' ].indexOf( select( 'core/editor' ).getEditedPostAttribute( 'status' ) ) !== -1,
		isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
	} ), [] );

	if ( isFetchingErrors ) {
		return (
			<SidebarNotification message={ fetchingErrorsMessage } isLoading={ true } />
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
