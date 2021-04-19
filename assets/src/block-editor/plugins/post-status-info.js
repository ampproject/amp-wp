/**
 * WordPress dependencies
 */
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import AMPToggle from '../../block-validation/components/amp-toggle';

export const name = 'amp-post-status-info';

/**
 * A wrapped AMP toggle component that is rendered for users with disabled Dev Tools.
 */
function WrappedAMPToggle() {
	const isDevToolsEnabled = useSelect( ( select ) => select( 'amp/block-editor' ).isDevToolsEnabled(), [] );

	/**
	 * When Dev Tools are enabled the `block-validation` shows the entire AMP
	 * sidebar and a notifications area.
	 */
	if ( isDevToolsEnabled ) {
		return null;
	}

	return (
		<PluginPostStatusInfo>
			<AMPToggle />
		</PluginPostStatusInfo>
	);
}

export const render = WrappedAMPToggle;
