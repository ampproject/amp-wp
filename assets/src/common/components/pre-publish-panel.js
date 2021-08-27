/**
 * WordPress dependencies
 */
import { PostFeaturedImage } from '@wordpress/editor';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';

/**
 * Adds a notice to the pre-publish panel for the featured image.
 *
 * @return {Function} Either a plain pre-publish panel, or the panel with a featured image notice.
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
