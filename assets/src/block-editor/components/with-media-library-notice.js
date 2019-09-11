/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { FeaturedImageToolbarSelect, getSelectMediaFrame } from '../../common/components/select-media-frame';
import { setImageFromURL } from '../../common/helpers';
import { dispatch } from '@wordpress/data';

const { wp } = window;

/**
 * Gets a wrapped version of MediaUpload to display a notice for small images.
 *
 * Only applies to the MediaUpload in the Featured Image component, PostFeaturedImage.
 *
 * @param {Function} InitialMediaUpload The MediaUpload component, passed from the filter.
 * @param {Object}   minImageDimensions Minimum required image dimensions.
 * @return {Function} The wrapped component.
 */
export default ( InitialMediaUpload, minImageDimensions ) => {
	const { width: EXPECTED_WIDTH, height: EXPECTED_HEIGHT } = minImageDimensions;

	/**
	 * Mostly copied from customize-controls.js, with slight changes.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/c80325658f85d24ff82295dd2d55bfdf789f4163/src/js/_enqueues/wp/customize/controls.js#L4695
	 * @see wp.media.HeaderControl
	 */
	return class FeaturedImageMediaUpload extends InitialMediaUpload {
		/**
		 * Constructs the class.
		 *
		 * @param {*} args Constructor arguments.
		 */
		constructor( ...args ) {
			super( ...args );

			// @todo This should be a different event.
			// This class should only be present in the MediaUpload for the Featured Image.
			if ( 'editor-post-featured-image__media-modal' === this.props.modalClass ) {
				this.initFeaturedImage = this.initFeaturedImage.bind( this );
				this.initFeaturedImage();
			}
		}

		/**
		 * Initialize.
		 *
		 * Mainly copied from customize-controls.js, like most of this class.
		 *
		 * Overwrites the Media Library frame, this.frame.
		 * Adds a suggested width and height.
		 */
		initFeaturedImage() {
			const FeaturedImageSelectMediaFrame = getSelectMediaFrame( FeaturedImageToolbarSelect );
			this.frame = new FeaturedImageSelectMediaFrame( {
				allowedTypes: this.props.allowedTypes,
				button: {
					text: __( 'Select', 'amp' ),
					close: false,
				},
				states: [
					new wp.media.controller.Library( {
						title: __( 'Choose image', 'amp' ),
						library: wp.media.query( { type: 'image' } ),
						multiple: false,
						date: false,
						priority: 20,
						// Note: These suggestions are shown in the media library image browser.
						suggestedWidth: EXPECTED_WIDTH,
						suggestedHeight: EXPECTED_HEIGHT,
					} ),
				],
			} );

			// See wp.media() for this.
			wp.media.frame = this.frame;

			this.frame.on( 'select', this.onSelectImage, this );
			this.frame.on( 'close', () => {
				this.initFeaturedImage();
			}, this );
		}

		/**
		 * Handles image selection.
		 */
		onSelectImage() {
			const attachment = this.frame.state().get( 'selection' ).first().toJSON();
			const dispatchImage = ( attachmentId ) => {
				dispatch( 'core/editor' ).editPost( { featured_media: attachmentId } );
			};
			const { onSelect } = this.props;
			const { url, id, width, height } = attachment;
			setImageFromURL( { url, id, width, height, onSelect, dispatchImage } );
			this.frame.close();
		}
	};
};
