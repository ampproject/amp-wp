/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, ExternalLink } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import AMPValidationErrorsKeptIcon from '../../../images/amp-validation-errors-kept.svg';
import { BLOCK_VALIDATION_STORE_KEY } from '../store';
import { StatusIcon } from '../icon';
import { SidebarNotification } from '../../block-editor/components/sidebar-notification';

/**
 * AMP validation status notification component.
 */
export default function AMPValidationStatusNotification() {
	const { autosave, savePost } = useDispatch( 'core/editor' );

	const {
		ampCompatibilityBroken,
		fetchingErrorsRequestErrorMessage,
		hasValidationErrors,
		isDraft,
		isEditedPostNew,
		isFetchingErrors,
		reviewLink,
	} = useSelect( ( select ) => ( {
		ampCompatibilityBroken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken(),
		fetchingErrorsRequestErrorMessage: select( BLOCK_VALIDATION_STORE_KEY ).getFetchingErrorsRequestErrorMessage(),
		hasValidationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors()?.length > 0,
		isDraft: [ 'draft', 'auto-draft' ].indexOf( select( 'core/editor' )?.getEditedPostAttribute( 'status' ) ) !== -1,
		isEditedPostNew: select( 'core/editor' ).isEditedPostNew(),
		isFetchingErrors: select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors(),
		reviewLink: select( BLOCK_VALIDATION_STORE_KEY ).getReviewLink(),
	} ), [] );

	if ( isFetchingErrors ) {
		return null;
	}

	if ( fetchingErrorsRequestErrorMessage ) {
		return (
			<SidebarNotification
				isError={ true }
				icon={ <AMPValidationErrorsKeptIcon /> }
				message={ fetchingErrorsRequestErrorMessage }
				action={
					<Button
						isLink
						onClick={ isDraft
							? () => savePost( { isPreview: true } )
							: () => autosave( { isPreview: true } )
						}
					>
						{ __( 'Try again', 'amp' ) }
					</Button>
				}
			/>
		);
	}

	if ( ampCompatibilityBroken ) {
		return (
			<SidebarNotification
				isError={ true }
				icon={ <AMPValidationErrorsKeptIcon /> }
				message={ __( 'AMP blocked from validation issues marked kept.', 'amp' ) }
				action={ reviewLink && (
					<ExternalLink href={ reviewLink }>
						{ __( 'View technical details', 'amp' ) }
					</ExternalLink>
				) }
			/>
		);
	}

	if ( hasValidationErrors ) {
		return (
			<SidebarNotification
				icon={ <StatusIcon broken={ true } /> }
				message={ __( 'AMP is working, but issues needs review.', 'amp' ) }
				action={ reviewLink && (
					<ExternalLink href={ reviewLink }>
						{ __( 'View technical details', 'amp' ) }
					</ExternalLink>
				) }
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
