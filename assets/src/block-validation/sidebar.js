/**
 * WordPress dependencies
 */
import { Button, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.css';
import { SidebarNotificationsContainer } from '../block-editor/components/sidebar-notification';
import { AMPRevalidateNotification, AMPValidationStatusNotification } from './amp-validation-status';
import { Error } from './error';
import { BLOCK_VALIDATION_STORE_KEY } from './store';

/**
 * Editor sidebar.
 */
export function Sidebar() {
	const { setIsShowingReviewed } = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const { isDraft, isShowingReviewed } = useSelect( ( select ) => ( {
		isDraft: [ 'draft', 'auto-draft' ].indexOf( select( 'core/editor' )?.getEditedPostAttribute( 'status' ) ) !== -1,
		isShowingReviewed: select( BLOCK_VALIDATION_STORE_KEY ).getIsShowingReviewed(),
	} ), [] );

	const { displayedErrors, reviewedValidationErrors, unreviewedValidationErrors, validationErrors } = useSelect( ( select ) => {
		let updatedDisplayedErrors;

		const updatedValidationErrors = select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors();
		const updatedReviewedValidationErrors = select( BLOCK_VALIDATION_STORE_KEY ).getReviewedValidationErrors();
		const updatedUnreviewedValidationErrors = select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors();

		if ( isShowingReviewed ) {
			updatedDisplayedErrors = updatedValidationErrors;
		} else {
			updatedDisplayedErrors = updatedUnreviewedValidationErrors;

			// If there are no unreviewed errors, we show the reviewed errors.
			if ( 0 === updatedDisplayedErrors.length ) {
				updatedDisplayedErrors = updatedReviewedValidationErrors;
			}
		}

		return {
			displayedErrors: updatedDisplayedErrors,
			reviewedValidationErrors: updatedReviewedValidationErrors,
			unreviewedValidationErrors: updatedUnreviewedValidationErrors,
			validationErrors: updatedValidationErrors,
		};
	}, [ isShowingReviewed ] );

	/**
	 * Focus the first focusable element when the sidebar opens.
	 */
	useEffect( () => {
		const element = document.querySelector( '.amp-sidebar a, .amp-sidebar button, .amp-sidebar input' );
		if ( element ) {
			element.focus();
		}
	}, [] );

	return (
		<div className="amp-sidebar">
			<SidebarNotificationsContainer isShady={ true }>
				<AMPValidationStatusNotification />
				<AMPRevalidateNotification />
			</SidebarNotificationsContainer>

			{ 0 < validationErrors.length && (
				0 < displayedErrors.length ? (
					<ul>
						{ displayedErrors.map( ( validationError, index ) => (
							<Error { ...validationError } key={ `${ validationError.clientId }${ index }` } />
						) ) }
					</ul>
				)
					: ! isDraft && (
						<PanelBody opened={ true }>
							<p>
								{ __( 'All AMP validation issues have been reviewed.', 'amp' ) }
							</p>
						</PanelBody>
					)
			) }

			{ ( 0 < reviewedValidationErrors.length && 0 < unreviewedValidationErrors.length ) && (
				<div className="amp-sidebar__options">
					<Button
						isLink
						onClick={ () => setIsShowingReviewed( ! isShowingReviewed ) }
					>
						{ isShowingReviewed
							? __( 'Hide reviewed issues', 'amp' )
							: __( 'Show reviewed issues', 'amp' ) }
					</Button>
				</div>
			) }
		</div>
	);
}
