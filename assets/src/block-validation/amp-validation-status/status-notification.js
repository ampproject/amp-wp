/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from '../store';
import AMPValidationErrorsKeptIcon from '../../../images/amp-validation-errors-kept.svg';
import { StatusIcon } from '../icon';
import { Loading } from '../../components/loading';
import { SidebarNotification } from '../../block-editor/components/sidebar-notification';

/**
 * AMP validation status notification component.
 */
export default function AMPValidationStatusNotification() {
	const {
		ampCompatibilityBroken,
		hasValidationErrors,
		isEditedPostNew,
		isFetchingErrors,
	} = useSelect( ( select ) => ( {
		ampCompatibilityBroken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken(),
		hasValidationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors()?.length > 0,
		isEditedPostNew: select( 'core/editor' ).isEditedPostNew(),
		isFetchingErrors: select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors(),
	} ), [] );

	if ( isFetchingErrors ) {
		return <Loading />;
	}

	if ( ampCompatibilityBroken ) {
		return (
			<SidebarNotification
				icon={ <AMPValidationErrorsKeptIcon /> }
				message={ __( 'AMP blocked from validation issues marked kept.', 'amp' ) }
				isError={ true }
			/>
		);
	}

	if ( hasValidationErrors ) {
		return (
			<SidebarNotification
				icon={ <StatusIcon broken={ true } /> }
				message={ __( 'AMP is working, but issues needs review.', 'amp' ) }
			/>
		);
	}

	if ( isEditedPostNew ) {
		return (
			<SidebarNotification
				icon={ <StatusIcon /> }
				message={ __( 'Validation issues will be checked for when the post is saved.', 'amp' ) }
			/>
		);
	}

	return (
		<SidebarNotification
			icon={ <StatusIcon /> }
			message={ __( 'AMP is working. All issues reviewed or removed.', 'amp' ) }
		/>
	);
}
