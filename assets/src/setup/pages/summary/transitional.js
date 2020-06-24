/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useContext } from '@wordpress/element';
import { ToggleControl } from '@wordpress/components';
import { Desktop } from '../../components/desktop';
import { Transitional as TransitionalIllustration } from '../../components/svg/transitional';
import { Options } from '../../components/options-context-provider';
import { MobileRedirect } from '../../components/svg/mobile-redirect';

/**
 * Summary screen when transitional mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Transitional( { currentTheme } ) {
	const { options, updateOptions } = useContext( Options );

	const { mobile_redirect: mobileRedirect } = options;

	return (
		<div className="transitional-summary">
			<div className="transitional-summary__summary selectable selectable--bottom">

				<div className="transitional-summary__summary-header">
					<div className="transitional-summary__illustration">
						<TransitionalIllustration />
					</div>
					<h4>
						{ __( 'Transitional', 'amp' ) }
					</h4>
				</div>

				<div className="transitional-summary__summary-body">
					<div>
						<p dangerouslySetInnerHTML={ { __html: __( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ) } } />
					</div>
				</div>
			</div>

			<div className="transitional-summary__summary selectable selectable--bottom">
				<div className="transitional-summary__summary-header">
					<h4>
						{ __( 'WordPress theme:', 'amp' ) }
						{ ' ' }
						{ currentTheme.name }
					</h4>
				</div>

				<div className="grid grid-1-2 transitional-summary__screenshot">
					<Desktop>
						<img
							src={ currentTheme.screenshot }
							alt={ currentTheme.name }
							loading="lazy"
							decoding="async"
							height="900"
							width="1200"
						/>
					</Desktop>
					<div>
						{ currentTheme.description }
					</div>
				</div>

			</div>

			<div className="selectable selectable--bottom transitional-summary__footer">
				<div>
					<ToggleControl
						checked={ true === mobileRedirect }
						label={ (
							<>
								<div className="transitional-summary__footer-illustration">
									<MobileRedirect />
								</div>
								<div className="transitional-summary__label-text">
									<h4>
										{ __( 'Redirect mobile visitors to AMP.', 'amp' ) }
									</h4>
									<p>
										{ __( 'AMP is not only for mobile.', 'amp' ) }
									</p>
								</div>
							</>
						) }
						onChange={ () => {
							updateOptions( { mobile_redirect: ! mobileRedirect } );
						} }
					/>
				</div>
			</div>
		</div>
	);
}

Transitional.propTypes = {
	currentTheme: PropTypes.shape( {
		description: PropTypes.string,
		name: PropTypes.string,
		screenshot: PropTypes.string,
		url: PropTypes.string,
	} ).isRequired,
};
