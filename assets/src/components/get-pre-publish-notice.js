/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getFeaturedImageMessage } from './';

export default ( validateImageSize, invalidSizeMessage ) => {
	return () => {
		const featuredImageMessage = getFeaturedImageMessage( validateImageSize, invalidSizeMessage );
		if ( ! featuredImageMessage ) {
			return PluginPrePublishPanel;
		}

		return (
			<Fragment>
				<PluginPrePublishPanel
					title={ __( 'Featured Image', 'amp' ) }
					initialOpen="true"
				>
					<Notice status="warning">
						<span>
							{ featuredImageMessage }
						</span>
					</Notice>
				</PluginPrePublishPanel>
			</Fragment>
		);
	};
};
