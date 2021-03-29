/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { Button, PanelRow } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import AMPValidationErrorsKeptIcon from '../../../../images/amp-validation-errors-kept.svg';
import { BLOCK_VALIDATION_STORE_KEY } from '../../store';
import { StatusIcon } from '../icon';
import { SidebarNotification } from '../sidebar-notification';
import { useAMPDocumentToggle } from '../../hooks/use-amp-document-toggle';
import { useErrorsFetchingStateChanges } from '../../hooks/use-errors-fetching-state-changes';
import { PLUGIN_NAME, SIDEBAR_NAME } from '../../plugins/amp-block-validation';
import AMPToggle from './amp-toggle';

/**
 * AMP document status notification component.
 */
export default function AMPDocumentStatusNotification() {
	const { openGeneralSidebar } = useDispatch( 'core/edit-post' );

	const { isAMPEnabled } = useAMPDocumentToggle();
	const { isFetchingErrors, fetchingErrorsMessage } = useErrorsFetchingStateChanges();

	const {
		keptMarkupValidationErrorCount,
		unreviewedValidationErrorCount,
	} = useSelect( ( select ) => ( {
		keptMarkupValidationErrorCount: select( BLOCK_VALIDATION_STORE_KEY ).getKeptMarkupValidationErrors().length,
		unreviewedValidationErrorCount: select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors().length,
	} ), [] );

	if ( ! isAMPEnabled ) {
		return (
			<AMPToggle />
		);
	}

	if ( isFetchingErrors ) {
		return (
			<>
				<AMPToggle />
				<SidebarNotification
					message={ fetchingErrorsMessage }
					isLoading={ true }
					isSmall={ true }
				/>
			</>
		);
	}

	if ( keptMarkupValidationErrorCount > 0 ) {
		return (
			<>
				<AMPToggle />
				<SidebarNotification
					icon={ <AMPValidationErrorsKeptIcon /> }
					message={
						sprintf(
							/* translators: %d is count of validation errors whose invalid markup is kept */
							_n(
								'AMP is blocked due to %d validation issue marked as kept.',
								'AMP is blocked due to %d validation issues marked as kept.',
								keptMarkupValidationErrorCount,
								'amp',
							),
							keptMarkupValidationErrorCount,
						)
					}
					isSmall={ true }
				/>
				<PanelRow>
					<Button
						onClick={ () => openGeneralSidebar( `${ PLUGIN_NAME }/${ SIDEBAR_NAME }` ) }
						isDefault={ true }
						isSmall={ true }
					>
						{ _n(
							'Review issue',
							'Review issues',
							keptMarkupValidationErrorCount,
							'amp',
						) }
					</Button>
				</PanelRow>
			</>
		);
	}

	if ( unreviewedValidationErrorCount > 0 ) {
		return (
			<>
				<AMPToggle />
				<SidebarNotification
					icon={ <StatusIcon broken={ true } /> }
					message={
						sprintf(
							/* translators: %d is count of unreviewed validation error */
							_n(
								'Your AMP page is working, but %d issue needs review.',
								'Your AMP page is working, but %d issues need review. ',
								unreviewedValidationErrorCount,
								'amp',
							),
							unreviewedValidationErrorCount,
						)
					}
					isSmall={ true }
				/>
				<PanelRow>
					<Button
						onClick={ () => openGeneralSidebar( `${ PLUGIN_NAME }/${ SIDEBAR_NAME }` ) }
						isDefault={ true }
						isSmall={ true }
					>
						{ _n(
							'Review issue',
							'Review issues',
							unreviewedValidationErrorCount,
							'amp',
						) }
					</Button>
				</PanelRow>
			</>
		);
	}

	return (
		<>
			<AMPToggle />
			<SidebarNotification
				icon={ <StatusIcon /> }
				message={ __( 'Your AMP page is working. All issues are reviewed or removed.', 'amp' ) }
				isSmall={ true }
			/>
		</>
	);
}
