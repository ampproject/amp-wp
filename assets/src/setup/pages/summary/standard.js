/**
 * External dependencies
 */
import PropTypes from 'prop-types';
/**
 * Internal dependencies
 */
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Desktop } from '../../components/desktop';

/**
 * Summary screen when standard mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Standard( { currentTheme } ) {
	return (
		<div className="standard-summary grid grid-1-1">
			<div className="standard-summary__description selectable selectable--bottom">
				<div className="standard-summary__description-header">
					<h1>
						{ __( 'Standard', 'amp' ) }
					</h1>
					<p dangerouslySetInnerHTML={ { __html: __( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific content types), and <b>it will use a single theme</b>. ', 'amp' ) } } />
				</div>
			</div>
			<div className="standard-summary__screenshot selectable selectable--bottom">

				<h3>
					{ __( 'WordPress theme: ' ) }
					{ ' ' }
					{ currentTheme.name }
				</h3>
				<Desktop>
					<img src={ currentTheme.screenshot } alt={ currentTheme.name } />
				</Desktop>
			</div>

			<aside className="plus-icon">
				<svg width="54" height="54" viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clipPath="url(#clip0)">
						<path d="M26.834 51.8789C40.6411 51.8789 51.834 40.686 51.834 26.8789C51.834 13.0718 40.6411 1.87891 26.834 1.87891C13.0269 1.87891 1.83398 13.0718 1.83398 26.8789C1.83398 40.686 13.0269 51.8789 26.834 51.8789Z" fill="white" stroke="#2759E7" strokeWidth="3" strokeMiterlimit="10" />
						<path d="M38.134 26.8789H26.834V38.1789" stroke="#2759E7" strokeWidth="3" strokeMiterlimit="10" strokeLinecap="round" />
						<path d="M38.134 26.8789H26.834V38.1789" stroke="#2759E7" strokeWidth="3" strokeMiterlimit="10" strokeLinecap="round" />
						<path d="M15.5342 26.8781H26.8342V15.5781" stroke="#2759E7" strokeWidth="3" strokeMiterlimit="10" strokeLinecap="round" />
					</g>
					<defs>
						<clipPath id="clip0">
							<rect width="53" height="53" fill="white" transform="translate(0.333984 0.378906)" />
						</clipPath>
					</defs>
				</svg>
			</aside>

		</div>
	);
}

Standard.propTypes = {
	currentTheme: PropTypes.shape( {
		description: PropTypes.string,
		name: PropTypes.string,
		screenshot: PropTypes.string,
		url: PropTypes.string,
	} ).isRequired,
};
