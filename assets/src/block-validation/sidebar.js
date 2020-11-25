/**
 * WordPress dependencies
 */
import { CheckboxControl, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.css';
import { Error } from './error';
import { BLOCK_VALIDATION_STORE_KEY } from './store';
import { BrokenIconSVG, NewTabIcon } from './icon';
import { AMP_VALIDITY_REST_FIELD_NAME } from './constants';

export function Sidebar() {
	const { setIsShowingReviewed } = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const { ampBroken, isShowingReviewed, reviewLink } = useSelect( ( select ) => ( {
		ampBroken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPBroken(),
		isShowingReviewed: select( BLOCK_VALIDATION_STORE_KEY ).getIsShowingReviewed(),
		reviewLink: select( 'core/editor' ).getEditedPostAttribute( AMP_VALIDITY_REST_FIELD_NAME ).review_link,
	} ) );

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
	 * Focus the focusable element when the sidebar opens.
	 */
	useEffect( () => {
		const element = document.querySelector( '.amp-sidebar a, .amp-sidebar button, .amp-sidebar input' );
		if ( element ) {
			element.focus();
		}
	}, [] );

	return (
		<div className="amp-sidebar">
			{
				ampBroken && (
					<PanelBody opened={ true } className="amp-sidebar__broken">
						<BrokenIconSVG />
						<p>
							{ __( 'AMP is broken at this URL because there are validation errors that have not been removed.', 'amp' ) }
						</p>
					</PanelBody>
				)
			}

			<PanelBody opened={ true }>
				<h2>
					{ __( 'Validation Errors', 'amp' ) }
				</h2>
				<p>
					{ __( 'The following AMP validation errors were found at this URL. ', 'amp' ) }
					{ reviewLink && (
						<a href={ reviewLink } className="amp-sidebar__review-link" target="_blank" rel="noreferrer">
							{ __( 'Review errors.', 'amp' ) }
							<NewTabIcon />
						</a>
					) }
				</p>
			</PanelBody>
			{ validationErrors.length === 0
				? (
					<PanelBody opened={ true }>
						<p>
							{ __( 'There are no AMP validation errors at this post\'s URL.', 'amp' ) }
						</p>
					</PanelBody>
				)
				:				(
					<>
						{ ( 0 < reviewedValidationErrors.length && 0 < unreviewedValidationErrors.length ) && (
							<PanelBody opened={ true }>
								<CheckboxControl
									checked={ isShowingReviewed }
									label={ __( 'Include previously reviewed errors', 'amp' ) }
									onChange={ ( newIsShowingReviewed ) => {
										setIsShowingReviewed( newIsShowingReviewed );
									} }
								/>

							</PanelBody>
						) }
						{ 0 < displayedErrors.length ? (
							<ul>
								{ displayedErrors.map( ( validationError, index ) => (
									<Error { ...validationError } key={ `${ validationError.clientId }${ index }` } />
								) ) }
							</ul>
						)
							: (
								<PanelBody opened={ true }>
									<p>
										{ __( 'There are no unreviewed AMP validation errors at this post\'s URL.', 'amp' ) }
									</p>
								</PanelBody>
							)
						}

					</>
				)
			}

		</div>
	);
}
