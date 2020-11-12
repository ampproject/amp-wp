/**
 * WordPress dependencies
 */
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.css';
import { Error } from './error';
import { BLOCK_VALIDATION_STORE_KEY } from './store';
import { BrokenIconSVG, NewTabIcon } from './icon';

export function Sidebar() {
	const { ampBroken, reviewLink, reviewedValidationErrors, unreviewedValidationErrors } = useSelect( ( select ) => ( {
		ampBroken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPBroken(),
		reviewLink: select( BLOCK_VALIDATION_STORE_KEY ).getReviewLink(),
		reviewedValidationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getReviewedValidationErrors(),
		unreviewedValidationErrors: select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors(),
	} ) );

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
					{ __( 'New Validation Errors', 'amp' ) }
				</h2>
				<p>
					{ __( 'The following AMP validation errors have not been marked as reviewed. ', 'amp' ) }
					{ reviewLink && (
						<a href={ reviewLink } className="amp-sidebar__review-link" target="_blank" rel="noreferrer">
							{ __( 'Review errors.', 'amp' ) }
							<NewTabIcon />
						</a>
					) }
				</p>
			</PanelBody>
			{ unreviewedValidationErrors && (
				<ul>
					{ unreviewedValidationErrors.map( ( validationError, index ) => (
						<Error { ...validationError } key={ `${ validationError.clientId }${ index }` } />
					) ) }
				</ul>
			) }
			<PanelBody opened={ true }>
				<h2>
					{ __( 'Reviewed Validation Errors', 'amp' ) }
				</h2>
				<p>
					{ __( 'The following AMP validation errors have been reviewed. ' ) }
					{ reviewLink && (
						<a href={ reviewLink } className="amp-sidebar__review-link" target="_blank" rel="noreferrer">
							{ __( 'Review again.', 'amp' ) }
							<NewTabIcon />
						</a>
					) }
				</p>
			</PanelBody>
			{ reviewedValidationErrors && (
				<ul>
					{ reviewedValidationErrors.map( ( validationError, index ) => (
						<Error { ...validationError } key={ `${ validationError.clientId }${ index }` } />
					) ) }
				</ul>
			) }
		</div>
	);
}
