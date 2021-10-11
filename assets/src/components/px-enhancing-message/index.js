/**
 * Internal dependencies
 */
import './style.css';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

export function PXEnhancingMessage() {
	return (
		<div className="extension-card-px-message">
			<span className="amp-logo-icon" />
			&nbsp;
			<span className="tooltiptext">
				{ __( 'This theme follow best practice and is known to work well with AMP plugin.', 'amp' ) }
			</span>
			{ __( 'Page Experience Enhancing', 'amp' ) }
		</div>
	);
}
