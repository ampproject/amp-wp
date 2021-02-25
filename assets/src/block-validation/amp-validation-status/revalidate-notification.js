/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';

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
	const { autosave, savePost } = useDispatch( 'core/editor' );

	const {
		hasActiveMetaboxes,
		isDraft,
		isFetchingErrors,
		isPostDirty,
	} = useSelect( ( select ) => ( {
		hasActiveMetaboxes: select( 'core/edit-post' ).hasMetaBoxes(),
		isDraft: [ 'draft', 'auto-draft' ].indexOf( select( 'core/editor' )?.getEditedPostAttribute( 'status' ) ) !== -1,
		isFetchingErrors: select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors(),
		isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
	} ), [] );

	if ( isFetchingErrors ) {
		return null;
	}

	// For posts where meta boxes are present, it's impossible to tell
	// if a meta box content has changed or not. Because of that, a dirty
	// state is ignored and it's always possible to save a post.
	// Likewise, we always display the re-validate message if there are
	// active meta boxes.
	if ( ! isPostDirty && ! hasActiveMetaboxes ) {
		return null;
	}

	return (
		<SidebarNotification
			icon={ <BellIcon /> }
			message={ hasActiveMetaboxes
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
