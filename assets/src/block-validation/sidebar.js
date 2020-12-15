/**
 * WordPress dependencies
 */
import { ToggleControl, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.css';
import AMPValidationErrorsIcon from '../../images/amp-validation-errors.svg';
import AMPValidationErrorsKeptIcon from '../../images/amp-validation-errors-kept.svg';
import { Error } from './error';
import { BLOCK_VALIDATION_STORE_KEY } from './store';
import { NewTabIcon } from './icon';
import { AMP_VALIDITY_REST_FIELD_NAME } from './constants';

/**
 * Editor sidebar.
 */
export function Sidebar() {
	const { setIsShowingReviewed } = useDispatch( BLOCK_VALIDATION_STORE_KEY );

	const { ampCompatibilityBroken, isShowingReviewed, published, reviewLink } = useSelect( ( select ) => ( {
		ampCompatibilityBroken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken(),
		isShowingReviewed: select( BLOCK_VALIDATION_STORE_KEY ).getIsShowingReviewed(),
		published: 'publish' === select( 'core/editor' )?.getEditedPostAttribute( 'status' ),
		// eslint-disable-next-line camelcase
		reviewLink: select( 'core/editor' ).getEditedPostAttribute( AMP_VALIDITY_REST_FIELD_NAME )?.review_link || null,
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
			{
				ampCompatibilityBroken && (
					<div className="amp-sidebar__broken-container">
						<div className="amp-sidebar__broken">
							<div className="amp-sidebar__validation-errors-kept-icon">
								<AMPValidationErrorsKeptIcon />
							</div>
							<div>
								<h3>
									{ __( 'Validation errors kept', 'amp' ) }
								</h3>
								{ __( 'This URL is not fully AMP-compatible.', 'amp' ) }
							</div>
						</div>
					</div>
				)
			}
			{ 0 < validationErrors.length && (
				<PanelBody opened={ true } className="amp-sidebar__description-panel">
					<div className="amp-sidebar__validation-errors-icon">
						<AMPValidationErrorsIcon />
					</div>
					<div className="amp-sidebar__validation-errors-heading">
						<h2>
							{ __( 'Validation Errors', 'amp' ) }
						</h2>

						<p>
							{ reviewLink && (
								<a href={ reviewLink } className="amp-sidebar__review-link" target="_blank" rel="noreferrer">
									{ __( 'Review errors', 'amp' ) }
									<NewTabIcon />
								</a>
							) }
						</p>
					</div>
					{ ( 0 < reviewedValidationErrors.length && 0 < unreviewedValidationErrors.length ) && (
						<div>
							<ToggleControl
								checked={ isShowingReviewed }
								label={ __( 'Include reviewed errors', 'amp' ) }
								onChange={ ( newIsShowingReviewed ) => {
									setIsShowingReviewed( newIsShowingReviewed );
								} }
							/>
						</div>
					) }
				</PanelBody>
			) }

			{
				! published && (
					<PanelBody opened={ true }>
						<p>
							{ __( 'This URL will be checked for validation errors when the post is published.', 'amp' ) }
						</p>
					</PanelBody>
				)
			}
			{ published && validationErrors.length === 0 && (
				<PanelBody opened={ true }>
					<p>
						{ __( 'There are no AMP validation errors at this post\'s URL.', 'amp' ) }
					</p>
				</PanelBody>
			) }

			{ published && 0 < validationErrors.length && (
				0 < displayedErrors.length ? (
					<ul>
						{ displayedErrors.map( ( validationError, index ) => (
							<Error { ...validationError } key={ `${ validationError.clientId }${ index }` } />
						) ) }
					</ul>
				)
					: published && (
						<PanelBody opened={ true }>
							<p>
								{ __( 'There are no unreviewed AMP validation errors at this post\'s URL.', 'amp' ) }
							</p>
						</PanelBody>
					)

			)
			}

		</div>
	);
}
