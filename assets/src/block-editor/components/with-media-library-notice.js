/**
 * External dependencies
 */
import { isFunction, get } from 'lodash';

/**
 * Internal dependencies
 */
import { FeaturedImageToolbarSelect } from '../../common/components/select-media-frame';

/**
 * Gets a wrapped version of MediaUpload to display a notice for small images.
 *
 * Only applies to the MediaUpload in the Featured Image component, PostFeaturedImage.
 *
 * @param {Function} InitialMediaUpload The MediaUpload component, passed from the filter.
 * @param {Object}   minImageDimensions Minimum required image dimensions.
 * @return {Function} The wrapped component.
 */
export default (InitialMediaUpload, minImageDimensions) => {
	if (!isFunction(InitialMediaUpload)) {
		return InitialMediaUpload;
	}

	const { width: EXPECTED_WIDTH, height: EXPECTED_HEIGHT } =
		minImageDimensions;

	/**
	 * Prepares the Featured Image toolbars and frames.
	 *
	 * @return {window.wp.media.view.MediaFrame.Select} The default media workflow.
	 */
	const getFeaturedImageMediaFrame = () => {
		const { wp } = window;

		return wp.media.view.MediaFrame.Select.extend({
			/**
			 * Create select toolbar.
			 *
			 * The only reason for this method is to override the select toolbar view class.
			 *
			 * Modified from {@link https://github.com/WordPress/wordpress-develop/blob/71cb3f861573f065c6d7d7ef1975bf98532239b4/src/js/media/views/frame/select.js#L167-L179}
			 *
			 * @override
			 *
			 * @param {Object} toolbar
			 * @param {Object} [options={}]
			 * @this {wp.media.controller.Region}
			 */
			createSelectToolbar(toolbar, options) {
				options = options || this.options.button || {};
				options.controller = this;
				options = {
					...options,
					allowedTypes: get(this, ['options', 'allowedTypes'], null),
				};

				toolbar.view = new FeaturedImageToolbarSelect(options);
			},

			/**
			 * Enables the Set Featured Image Button.
			 *
			 * @param {Object} toolbar toolbar for featured image state
			 * @return {void}
			 */
			featuredImageToolbar(toolbar) {
				this.createSelectToolbar(toolbar, {
					text: wp.media.view.l10n.setFeaturedImage,
					state: this.options.state,
				});
			},

			/**
			 * Handle the edit state requirements of selected media item.
			 *
			 * @return {void}
			 */
			editState() {
				const selection = this.state('featured-image').get('selection');
				const view = new wp.media.view.EditImage({
					model: selection.single(),
					controller: this,
				}).render();

				// Set the view to the EditImage frame using the selected image.
				this.content.set(view);

				// After bringing in the frame, load the actual editor via an ajax call.
				view.loadEditor();
			},

			/**
			 * Create the default states.
			 *
			 * @return {void}
			 */
			createStates: function createStates() {
				this.on(
					'toolbar:create:featured-image',
					this.featuredImageToolbar,
					this
				);
				this.on('content:render:edit-image', this.editState, this);

				const FeaturedImageLibrary =
					wp.media.controller.FeaturedImage.extend({
						defaults: {
							...wp.media.controller.FeaturedImage.prototype
								.defaults,
							date: false,
							filterable: false,
							// Note: These suggestions are shown in the media library image browser.
							suggestedWidth: EXPECTED_WIDTH,
							suggestedHeight: EXPECTED_HEIGHT,
						},
					});

				this.states.add([
					new FeaturedImageLibrary(),
					new wp.media.controller.EditImage({
						model: this.options.editImage,
					}),
				]);
			},
		});
	};

	/**
	 * Get attachment collection.
	 *
	 * @param {Array} ids Attachment IDs.
	 * @return {Object}     The attachment collection.
	 */
	const getAttachmentsCollection = (ids) => {
		const { wp } = window;

		return wp.media.query({
			order: 'ASC',
			orderby: 'post__in',
			post__in: ids,
			posts_per_page: -1,
			query: true,
			type: 'image',
		});
	};

	/**
	 * Extends the MediaUpload component to display a notice for small images.
	 */
	return class FeaturedImageMediaUpload extends InitialMediaUpload {
		/**
		 * Initializes the Media Library requirements for the featured image flow.
		 *
		 * @override
		 * @description Overrides the media upload component's initialize method for featured image.
		 *
		 * Modified from {@link https://github.com/WordPress/gutenberg/blob/debddee2ace15263c08c66b5f5a43a9e17bf5d0c/packages/media-utils/src/components/media-upload/index.js#L301-L316|Original MediaUpload buildAndSetFeatureImageFrame method}.
		 *
		 * @return {void}
		 */
		buildAndSetFeatureImageFrame() {
			const { wp } = window;
			const FeaturedImageFrame = getFeaturedImageMediaFrame();
			const attachments = getAttachmentsCollection(this.props.value);
			const selection = new wp.media.model.Selection(attachments.models, {
				props: attachments.props.toJSON(),
			});
			this.frame = new FeaturedImageFrame({
				mimeType: this.props.allowedTypes,
				state: 'featured-image',
				multiple: this.props.multiple,
				selection,
				editing: Boolean(this.props.value),
			});
			wp.media.frame = this.frame;
		}
	};
};
