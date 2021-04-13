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
		isDraft,
		isPostDirty,
		maybeIsPostDirty,
	} = useSelect( ( select ) => ( {
		isDraft: [ 'draft', 'auto-draft' ].indexOf( select( 'core/editor' ).getEditedPostAttribute( 'status' ) ) !== -1,
		isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
		maybeIsPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getMaybeIsPostDirty(),
	} ), [] );

	if ( isFetchingErrors ) {
		return (
			<SidebarNotification message={ fetchingErrorsMessage } isLoading={ true } />
		);
	}

	if ( ! isPostDirty && ! maybeIsPostDirty ) {
		return null;
	}

	return (
		<SidebarNotification
			icon={ <BellIcon /> }
			message={ maybeIsPostDirty
				? __( 'Content may have changed.', 'amp' )
				: __( 'Content has changed.', 'amp' ) }
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
