/**
 * External dependencies
 */
import { now } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import FeaturedImageSelectMediaFrame from './featured-image-select-media-frame';
import FeaturedImageCropper from './featured-image-cropper';

const { wp } = window;

/**
 * Gets a wrapped version of MediaUpload to crop featured images.
 *
 * Only applies to the MediaUpload in the Featured Image component, PostFeaturedImage.
 * Suggests cropping of the featured image if it's not 696 x 928.
 * Mostly copied from customize-controls.js.
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
	 * @see wp.media.HeaderControl
	 */
	return class FeaturedImageMediaUpload extends InitialMediaUpload {
		/**
		 * Constructs the class.
		 */
		constructor() {
			super( ...arguments );

			// @todo This should be a different event.
			// This class should only be present in the MediaUpload for the Featured Image.
			if ( this.props.modalClass && 'editor-post-featured-image__media-modal' === this.props.modalClass ) {
				this.init = this.init.bind( this );
				this.init();
			}
		}

		/**
		 * Initialize.
		 *
		 * Mainly copied from customize-controls.js, like most of this class.
		 *
		 * Overwrites the Media Library frame, this.frame.
		 * Adds the ability to crop the featured image.
		 *
		 * @see wp.media.CroppedImageControl.initFrame
		 */
		init() {
			this.frame = new FeaturedImageSelectMediaFrame( {
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
					new FeaturedImageCropper( {
						imgSelectOptions: this.calculateImageSelectOptions,
						control: this,
					} ),
				],
			} );

			// See wp.media() for this.
			wp.media.frame = this.frame;

			this.frame.on( 'select', this.onSelectImage, this );
			this.frame.on( 'cropped', this.onCropped, this );
			this.frame.on( 'skippedcrop', this.onSkippedCrop, this );
			this.frame.on( 'close', () => {
				this.init();
			}, this );
		}

		/**
		 * Calculate image selection options.
		 *
		 * Returns a set of options, computed from the attached image data and
		 * control-specific data, to be fed to the imgAreaSelect plugin in
		 * wp.media.view.Cropper.
		 *
		 * @param {wp.media.model.Attachment} attachment   Attachment.
		 * @param {wp.media.controller.Cropper} controller Controller.
		 * @return {Object} Options
		 */
		calculateImageSelectOptions( attachment, controller ) {
			const realWidth = attachment.get( 'width' ),
				realHeight = attachment.get( 'height' );

			let xInit = parseInt( EXPECTED_WIDTH, 10 ),
				yInit = parseInt( EXPECTED_HEIGHT, 10 );

			const ratio = xInit / yInit,
				xImg = xInit,
				yImg = yInit;

			// Allow cropping to be skipped because the image is at least the required dimensions, so skipping crop will auto crop.
			controller.set( 'canSkipCrop', true );

			if ( realWidth / realHeight > ratio ) {
				yInit = realHeight;
				xInit = yInit * ratio;
			} else {
				xInit = realWidth;
				yInit = xInit / ratio;
			}

			const x1 = ( realWidth - xInit ) / 2,
				y1 = ( realHeight - yInit ) / 2;

			return {
				aspectRatio: xInit + ':' + yInit,
				handles: true,
				keys: true,
				instance: true,
				persistent: true,
				imageWidth: realWidth,
				imageHeight: realHeight,
				minWidth: xImg > xInit ? xInit : xImg,
				minHeight: yImg > yInit ? yInit : yImg,
				x1: x1, // eslint-disable-line object-shorthand
				y1: y1, // eslint-disable-line object-shorthand
				x2: xInit + x1,
				y2: yInit + y1,
			};
		}

		/**
		 * Handle image selection.
		 *
		 * After an image is selected in the media modal, switch to the cropper state if the image isn't the right size.
		 * Only an image that is at least the expected width/height can be selected in the first place.
		 */
		onSelectImage() {
			const attachment = this.frame.state().get( 'selection' ).first().toJSON();
			if ( EXPECTED_WIDTH === attachment.width && EXPECTED_HEIGHT === attachment.height ) {
				this.setImageFromURL( attachment.url, attachment.id, attachment.width, attachment.height );
				this.frame.close();
			} else {
				this.frame.setState( 'cropper' );
			}
		}

		/**
		 * Whether there should be an option to crop at all.
		 *
		 * If an image has a width of less than 696 or a height of less than 928,
		 * there's no way to have the correct image size without distorting it.
		 * So this can allow preventing the crop option when choosing a featured image.
		 *
		 * @param {Object} attachment The attachment object to evaluate.
		 *
		 * @return {boolean} Whether to allow cropping.
		 */
		doAllowCrop( attachment ) {
			return ( attachment.width && attachment.height && attachment.width >= EXPECTED_WIDTH && attachment.height >= EXPECTED_HEIGHT );
		}

		/**
		 * Return whether the image must be cropped, based on required dimensions.
		 *
		 * @param {number} dstW The expected width.
		 * @param {number} dstH The expected height.
		 * @param {number} imgW The actual width.
		 * @param {number} imgH The actual height.
		 *
		 * @return {boolean} Whether the image must be cropped.
		 */
		mustBeCropped( dstW, dstH, imgW, imgH ) {
			return ! (
				( dstW === imgW && dstH === imgH ) ||
				( imgW <= dstW )
			);
		}

		/**
		 * After the image has been cropped, apply the cropped image data to the setting.
		 *
		 * @param {Object} croppedImage Cropped attachment data.
		 */
		onCropped( croppedImage ) {
			const url = croppedImage.url,
				attachmentId = croppedImage.id,
				width = croppedImage.width,
				height = croppedImage.height;
			this.setImageFromURL( url, attachmentId, width, height );
		}

		/**
		 * If cropping was skipped, apply the image data directly to the setting.
		 *
		 * @param {Object} selection Selection.
		 */
		onSkippedCrop( selection ) {
			const url = selection.get( 'url' ),
				width = selection.get( 'width' ),
				height = selection.get( 'height' );
			this.setImageFromURL( url, selection.id, width, height );
		}

		/**
		 * Set the featured image.
		 *
		 * @param {string} url          Image URL.
		 * @param {number} attachmentId Attachment ID.
		 * @param {number} width        Image width.
		 * @param {number} height       Image height.
		 */
		setImageFromURL( url, attachmentId, width, height ) {
			const data = {};
			const { onSelect } = this.props;

			data.url = url;
			data.thumbnail_url = url;
			data.timestamp = now();

			if ( attachmentId ) {
				data.attachment_id = attachmentId;
			}

			if ( width ) {
				data.width = width;
			}

			if ( height ) {
				data.height = height;
			}

			onSelect( data ); // @todo Does this do anything?
			dispatch( 'core/editor' ).editPost( { featured_media: attachmentId } );
		}
	};
};
