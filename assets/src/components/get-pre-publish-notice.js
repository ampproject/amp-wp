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
import { getFeaturedImageMessage } from '../helpers';

/**
 * Conditionally adds a notice to the pre-publish panel for the featured image.
 *
 * @param {Function} validateImageSize A function to validate whether the size is correct.
 * @param {string} invalidSizeMessage A message to display in a Notice if the size is wrong.
 * @return {Function} Either a plain pre-publish panel, or the panel with a featured image notice.
 */
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
