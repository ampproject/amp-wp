/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const debugOptions = {
	'disable-post': __( 'Disable post processing', 'amp' ),
	'disable-cache': __( 'Disable response cache', 'amp' ),
	'prevent-redirect': __( 'Prevent redirect', 'amp' ),
	'reject-all-errors': __( 'Reject all errors', 'amp' ),
	'accept-excessive-css': __( 'Accept excessive CSS', 'amp' ),
	'disable-amp': __( 'Disable AMP', 'amp' ),
	'disable-tree-shaking': __( 'Disable tree shaking', 'amp' ),
};

/**
 * Adds a submenu of debug options to the admin bar's AMP submenu.
 *
 * @return {Function} The component.
 */
const DebugOptions = () => {
	const listItems = [];
	for ( const [ debugId, title ] of Object.entries( debugOptions ) ) {
		listItems.push( (
			<li id={ `wp-admin-bar-amp-${ debugId }` } data-ampdevmode>
				<div className="ab-item">
					{ title }
				</div>
			</li>
		) );
	}

	return listItems;
};

export default DebugOptions;
