/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Adds a submenu of debug options to the admin bar's AMP submenu.
 *
 * @return {Function} The component.
 */
const DebugOptions = () => {
	return (
		<>
			<li id="wp-admin-bar-amp-disable-post" data-ampdevmode>
				<div className="ab-item">
					{ __( 'Disable post processing', 'amp' ) }
				</div>
			</li>
			<li id="wp-admin-bar-amp-disable-cache" data-ampdevmode>
				<div className="ab-item">
					{ __( 'Disable response cache', 'amp' ) }
				</div>
			</li>
		</>
	);
};

export default DebugOptions;
