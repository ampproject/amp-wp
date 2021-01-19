/**
 * WordPress dependencies
 */
import {
	Button,
	ExternalLink,
	PanelBody,
	PanelRow,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.css';
import AMPValidationErrorsIcon from '../../images/amp-validation-errors.svg';
import AMPValidationErrorsKeptIcon from '../../images/amp-validation-errors-kept.svg';
import { Loading } from '../components/loading';
import { Error } from './error';
import { BLOCK_VALIDATION_STORE_KEY } from './store';

/**
 * Editor sidebar.
 */
export function Sidebar() {
	const { setIsShowingReviewed } = useDispatch( BLOCK_VALIDATION_STORE_KEY );
	const { savePost } = useDispatch( 'core/editor' );

	const { ampCompatibilityBroken, isDirty, isFetchingErrors, isShowingReviewed, status, reviewLink } = useSelect( ( select ) => ( {
		ampCompatibilityBroken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken(),
		isDirty: select( 'core/editor' )?.isEditedPostDirty(),
		isFetchingErrors: select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors(),
		isShowingReviewed: select( BLOCK_VALIDATION_STORE_KEY ).getIsShowingReviewed(),
		status: select( 'core/editor' )?.getEditedPostAttribute( 'status' ),
		reviewLink: select( BLOCK_VALIDATION_STORE_KEY ).getReviewLink(),
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

	const saved = 'auto-draft' !== status;
	const forceRevalidate = () => savePost( { preview: true } );

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
									{ __( 'Invalid markup kept', 'amp' ) }
								</h3>
								{ __( 'The permalink will not be served as valid AMP.', 'amp' ) }
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
							{ __( 'Validation Issues', 'amp' ) }
						</h2>

						<p>
							{ reviewLink && (
								<ExternalLink href={ reviewLink } className="amp-sidebar__review-link">
									{ __( 'View technical details', 'amp' ) }
								</ExternalLink>
							) }
						</p>
					</div>
					{ ( 0 < reviewedValidationErrors.length && 0 < unreviewedValidationErrors.length ) && (
						<div className="amp-sidebar__options">
							<ToggleControl
								checked={ isShowingReviewed }
								label={ __( 'Include reviewed issues', 'amp' ) }
								onChange={ ( newIsShowingReviewed ) => {
									setIsShowingReviewed( newIsShowingReviewed );
								} }
							/>
						</div>
					) }
				</PanelBody>
			) }

			{
				! saved && 0 === validationErrors.length && (
					<PanelBody opened={ true }>
						{ isFetchingErrors ? <Loading /> : (
							<>
								<PanelRow>
									<p>
										{ __( 'Validation issues will be checked for when the post is saved.', 'amp' ) }
									</p>
								</PanelRow>
								{ isDirty && (
									<PanelRow>
										<Button isSecondary onClick={ forceRevalidate }>
											{ __( 'Save draft and validate now', 'amp' ) }
										</Button>
									</PanelRow>
								) }
							</>
						) }
					</PanelBody>
				)
			}
			{ saved && validationErrors.length === 0 && (
				<PanelBody opened={ true }>
					{ isFetchingErrors ? <Loading /> : (
						<p>
							{ __( 'There are no AMP validation issues.', 'amp' ) }
						</p>
					) }
				</PanelBody>
			) }

			{ isDirty && saved && (
				<PanelBody opened={ true }>
					<PanelRow>
						<p>
							{ __( 'The post content has been modified since the last AMP validation.', 'amp' ) }
						</p>
					</PanelRow>
					<PanelRow>
						<Button isSecondary onClick={ forceRevalidate }>
							{ __( 'Re-validate now', 'amp' ) }
						</Button>
					</PanelRow>
				</PanelBody>
			) }

			{ 0 < validationErrors.length && (
				0 < displayedErrors.length ? (
					<ul>
						{ displayedErrors.map( ( validationError, index ) => (
							<Error { ...validationError } key={ `${ validationError.clientId }${ index }` } />
						) ) }
					</ul>
				)
					: saved && (
						<PanelBody opened={ true }>
							{ isFetchingErrors ? <Loading /> : (
								<p>
									{ __( 'All AMP validation issues have been reviewed.', 'amp' ) }
								</p>
							) }
						</PanelBody>
					)
			) }

		</div>
	);
}
