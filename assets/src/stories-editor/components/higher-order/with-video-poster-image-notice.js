
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { Notice } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Higher-order component that is used for adding an error notice for the poster image control for video blocks in AMP stories.
 *
 * Note that if a user has selected a poster image and then removed it, the poster attribute will remain but be empty.
 * Because of this, an AMP validation error will not not always ensue if no poster image is supplied, as the plugin's sanitizer
 * will supply an empty poster attribute to try to prevent a validation error from happening. The  "amp-story >> amp-video" spec
 * has a loose definition for amp-video[poster] in that only specifies that it is mandatory, but it leaves its value as being undefined.
 * So this is why an empty poster attribute actually circumvents a validation error from happening. In the future if the value_url type
 * is specified, then the sanitizer will not be able to supply an empty value to circumvent the AMP validation error.
 *
 * @link https://github.com/ampproject/amphtml/blob/b814e5d74cadf554c5caa1233d71e8e840788ff5/extensions/amp-video/validator-amp-video.protoascii#L147-L150
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( MediaUpload ) => {
		return ( props ) => {
			const isAmpStory = 'amp_story' === select( 'core/editor' ).getCurrentPostType();
			const selectedBlock = select( 'core/block-editor' ).getSelectedBlock();

			// The `! selectedBlock.attributes.src` check ensures that the notice is not added to the block placeholder.
			// Otherwise, if a video has been selected, then it is presumed that the MediaUpload here is for the poster image.
			// There is apparently no way to know for sure that this is for the poster, other than by checking if
			// props.title === "Select Poster Image", but this would be quite brittle.
			if ( ! isAmpStory || ! selectedBlock || 'core/video' !== selectedBlock.name || ! selectedBlock.attributes.src || selectedBlock.attributes.poster ) {
				return <MediaUpload { ...props } />;
			}

			return (
				<>
					<Notice
						status="error"
						isDismissible={ false }
					>
						{ __( 'A poster is required for videos in stories.', 'amp' ) }
					</Notice>
					<MediaUpload { ...props } />
				</>
			);
		};
	},
	'withVideoPosterImageNotice'
);
