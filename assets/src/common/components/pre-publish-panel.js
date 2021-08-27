/**
 * WordPress dependencies
 */
import { PostFeaturedImage } from '@wordpress/editor';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';

/**
 * Adds a pre-publish panel containing the featured image selection component.
 *
 * Note: The `PostFeaturedImage` component would have already been filtered to include
 * any notices for the featured image so there is no need to recreate them here.
 *
 * @return {Function} A pre-publish panel containing the featured image selection component.
 */
const PrePublishPanel = () => {
	return (
		<PluginPrePublishPanel
			title={ __( 'Featured Image', 'amp' ) }
			initialOpen="true"
		>
			<PostFeaturedImage />
		</PluginPrePublishPanel>
	);
};

PrePublishPanel.propTypes = {};

export default PrePublishPanel;
