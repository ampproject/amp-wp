
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { Fragment } from '@wordpress/element';
import { Notice } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Higher-order component that is used for adding an error notice for the poster image control for video blocks in AMP stories.
 *
 * Note that technically an AMP validation error will not ensue if no poster image is supplied, as the plugin's sanitizer
 * will supply an empty poster attribute to try to prevent a validation error from happening. This works because the
 * "amp-story >> amp-video" spec has a loose definition for amp-video[poster] in that only specifies that it is mandatory,
 * but it leaves its value as being undefined. In the future if the value_url type is specified, then the sanitizer will
 * not be able to supply an empty value to circumvent the AMP validation error.
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

			if ( ! isAmpStory || ! selectedBlock || 'core/video' !== selectedBlock.name || selectedBlock.attributes.poster ) {
				return <MediaUpload { ...props } />;
			}

			return (
				<Fragment>
					<Notice
						status="warning"
						isDismissible={ false }
					>
						{ __( 'A poster is required for videos in stories.', 'amp' ) }
					</Notice>
					<MediaUpload { ...props } />
				</Fragment>
			);
		};
	},
	'withVideoPosterImageNotice'
);
