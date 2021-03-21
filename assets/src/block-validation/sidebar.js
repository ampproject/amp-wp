/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
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

	const {
		displayedErrors,
		hasReviewedValidationErrors,
		isShowingReviewed,
	} = useSelect( ( select ) => {
		const _isShowingReviewed = select( BLOCK_VALIDATION_STORE_KEY ).getIsShowingReviewed();

		return {
			displayedErrors: _isShowingReviewed
				? select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors()
				: select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors(),
			hasReviewedValidationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getReviewedValidationErrors()?.length > 0,
			isShowingReviewed: _isShowingReviewed,
		};
	}, [] );

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
				<AMPRevalidateNotification />
				<AMPValidationStatusNotification />
			</SidebarNotificationsContainer>

			{ 0 < displayedErrors.length && (
				<ul className="amp-sidebar__errors-list">
					{ displayedErrors.map( ( validationError, index ) => (
						<li
							// Add `index` to key since not all errors have `clientId`.
							key={ `${ validationError.clientId }${ index }` }
							className="amp-sidebar__errors-list-item"
						>
							<Error { ...validationError } />
						</li>
					) ) }
				</ul>
			) }

			{ hasReviewedValidationErrors && (
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
