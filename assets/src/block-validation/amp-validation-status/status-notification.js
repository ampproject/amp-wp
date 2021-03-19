/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
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
		fetchingErrorsRequestErrorMessage,
		isDraft,
		isEditedPostNew,
		isFetchingErrors,
		keptMarkupValidationErrorCount,
		reviewLink,
		unreviewedValidationErrorCount,
		validationErrorCount,
	} = useSelect( ( select ) => ( {
		fetchingErrorsRequestErrorMessage: select( BLOCK_VALIDATION_STORE_KEY ).getFetchingErrorsRequestErrorMessage(),
		isDraft: [ 'draft', 'auto-draft' ].indexOf( select( 'core/editor' )?.getEditedPostAttribute( 'status' ) ) !== -1,
		isEditedPostNew: select( 'core/editor' ).isEditedPostNew(),
		isFetchingErrors: select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors(),
		keptMarkupValidationErrorCount: select( BLOCK_VALIDATION_STORE_KEY ).getKeptMarkupValidationErrors().length,
		reviewLink: select( BLOCK_VALIDATION_STORE_KEY ).getReviewLink(),
		unreviewedValidationErrorCount: select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors().length,
		validationErrorCount: select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors().length,
	} ), [] );

	if ( isFetchingErrors ) {
		return null;
	}

	if ( fetchingErrorsRequestErrorMessage ) {
		return (
			<SidebarNotification
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

	if ( keptMarkupValidationErrorCount > 0 ) {
		return (
			<SidebarNotification
				icon={ <AMPValidationErrorsKeptIcon /> }
				message={
					sprintf(
						/* translators: %d is count of validation errors whose invalid markup is kept */
						_n(
							'AMP is disabled due to invalid markup being kept for %d issue.',
							'AMP is disabled due to invalid markup being kept for %d issues.',
							keptMarkupValidationErrorCount,
							'amp',
						),
						keptMarkupValidationErrorCount,
					)
				}
				action={ reviewLink && (
					<ExternalLink href={ reviewLink }>
						{ __( 'View technical details', 'amp' ) }
					</ExternalLink>
				) }
			/>
		);
	}

	if ( unreviewedValidationErrorCount > 0 ) {
		return (
			<SidebarNotification
				icon={ <StatusIcon broken={ true } /> }
				message={
					sprintf(
						/* translators: %d is count of unreviewed validation error */
						_n(
							'AMP is enabled, but %d issue needs review.',
							'AMP is enabled, but %d issues need review.',
							unreviewedValidationErrorCount,
							'amp',
						),
						unreviewedValidationErrorCount,
					)
				}
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
				message={ __( 'Validation will be checked upon saving.', 'amp' ) }
			/>
		);
	}

	if ( validationErrorCount > 0 ) {
		return <SidebarNotification
			icon={ <StatusIcon /> }
			message={
				sprintf(
					/* translators: %d is count of unreviewed validation error */
					_n(
						'AMP is enabled, and %d validation issue has been reviewed.',
						'AMP is enabled, and %d validation issues have been reviewed.',
						validationErrorCount,
						'amp',
					),
					validationErrorCount,
				)
			}
			action={ reviewLink && (
				<ExternalLink href={ reviewLink }>
					{ __( 'View technical details', 'amp' ) }
				</ExternalLink>
			) }
		/>;
	}

	return (
		<SidebarNotification
			icon={ <StatusIcon /> }
			message={ __( 'AMP is enabled. There are no validation issues.', 'amp' ) }
		/>
	);
}
