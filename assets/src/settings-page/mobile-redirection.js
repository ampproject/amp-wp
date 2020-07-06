
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { RedirectToggle } from '../components/redirect-toggle';

export function MobileRedirection() {
	return (
		<div className="mobile-redirection">
			<h2>
				{ __( 'Mobile Redirection', 'amp' ) }
			</h2>
			<RedirectToggle />
		</div>
	);
}
