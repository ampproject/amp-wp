/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';

/**
 * External dependencies
 */
import { now } from 'lodash';

/**
 * Internal dependencies
 */
import { getMinimumStoryPosterDimensions, getMinimumFeaturedImageDimensions } from '../helpers';

const EXPECTED_WIDTH = 696; // @todo This may be wrong as 1200px is expected for featured image?
const EXPECTED_HEIGHT = 928;

const getMinimumDimensions = () => {
	const posterImageDimensions = getMinimumStoryPosterDimensions();
	const featuredImageDimensions = getMinimumFeaturedImageDimensions();

	//( posterImageDimensions.height / posterImageDimensions.width ) *
	let width = Math.max( posterImageDimensions.width, featuredImageDimensions.width );
	let height = Math.max( posterImageDimensions.height, featuredImageDimensions.height );

	// @todo Adjust the width or height to make sure the aspect ratio of posterImageDimensions is preserved.
	if ( width / height ) {

	}



};

/**
 * Gets a wrapped version of MediaUpload to crop images for AMP Stories.
 *
 * Only applies to the MediaUpload in the Featured Image component, PostFeaturedImage.
 * Suggests cropping of the featured image if it's not 696 x 928.
 * Mostly copied from customize-controls.js.
 *
 * @param {Function} InitialMediaUpload The MediaUpload component, passed from the filter.
 * @return {Function} The wrapped component.
 */
export default ( InitialMediaUpload ) => {
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

			// This class should only be present in the MediaUpload for the Featured Image.
			if ( this.props.modalClass && 'editor-post-featured-image__media-modal' === this.props.modalClass ) {
				this.init = this.init.bind( this );
				this.init();
			}
		}

		/**
		 * Mainly copied from customize-controls.js, like most of this class.
		 *
		 * Overwrites the Media Library frame, this.frame.
		 * Adds the ability to crop the featured image.
		 *
		 * @see wp.media.CroppedImageControl.initFrame
		 */
		init() {
			this.frame = wp.media( {
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
						// @todo minWidth:
						// @todo minHeight:
						// @todo ratio?
						suggestedWidth: EXPECTED_WIDTH,
						suggestedHeight: EXPECTED_HEIGHT,
					} ),
					new wp.media.controller.Cropper( {
						imgSelectOptions: this.calculateImageSelectOptions,
						control: this,
					} ),
				],
			} );

			this.frame.on( 'select', this.onSelectImage, this );
			this.frame.on( 'cropped', this.onCropped, this );
			this.frame.on( 'skippedcrop', this.onSkippedCrop, this );
			this.frame.on( 'close', () => {
				this.init();
			}, this );
		}

		/**
		 * Returns a set of options, computed from the attached image data and
		 * control-specific data, to be fed to the imgAreaSelect plugin in
		 * wp.media.view.Cropper.
		 *
		 * @param {wp.media.model.Attachment} attachment
		 * @param {wp.media.controller.Cropper} controller
		 * @return {Object} Options
		 */
		calculateImageSelectOptions( attachment, controller ) {
			const control = controller.get( 'control' ),
				realWidth = attachment.get( 'width' ),
				realHeight = attachment.get( 'height' );

			let xInit = parseInt( EXPECTED_WIDTH, 10 ),
				yInit = parseInt( EXPECTED_HEIGHT, 10 );

			const ratio = xInit / yInit,
				xImg = xInit,
				yImg = yInit;

			controller.set( 'canSkipCrop', ! control.mustBeCropped( xInit, yInit, realWidth, realHeight ) );

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
		 * After an image is selected in the media modal, switch to the cropper
 		 * state if the image isn't the right size.
 		*/
		onSelectImage() {
			const attachment = this.frame.state().get( 'selection' ).first().toJSON();

			if ( ! this.doAllowCrop( attachment ) ) {
				this.setImageFromURL( attachment.url, attachment.id, attachment.width, attachment.height );
				this.frame.close();
				return;
			}

			if ( EXPECTED_WIDTH === attachment.width && EXPECTED_HEIGHT === attachment.height ) {
				wp.ajax.post( 'crop-image', {
					nonce: attachment.nonces.edit,
					id: attachment.id,
					context: 'site-icon',
					cropDetails: {
						x1: 0,
						y1: 0,
						width: EXPECTED_WIDTH,
						height: EXPECTED_HEIGHT,
						dst_width: EXPECTED_WIDTH,
						dst_height: EXPECTED_HEIGHT,
					},
				} ).done( ( croppedImage ) => {
					this.setImageFromURL( croppedImage.url, croppedImage.id, croppedImage.width, croppedImage.height );
					this.frame.close();
				} ).fail( () => {
					this.frame.trigger( 'content:error:crop' );
				} );
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
		 * @return {boolean} Whether to allow cropping.
		 */
		doAllowCrop( attachment ) {
			return ( attachment.width && attachment.height && attachment.width >= EXPECTED_WIDTH && attachment.height >= EXPECTED_HEIGHT );
		}

		/**
		 * Return whether the image must be cropped, based on required dimensions.
		 *
		 * @param {number}  dstW The expected width.
		 * @param {number}  dstH The expected height.
		 * @param {number}  imgW The actual width.
		 * @param {number}  imgH The actual height.
		 * @return {boolean} Whether the image must be cropped.
		 */
		mustBeCropped( dstW, dstH, imgW, imgH ) {
			if ( ( dstW === imgW && dstH === imgH ) || ( imgW <= dstW )	) {
				return false;
			}

			return true;
		}

		/**
		 * After the image has been cropped, apply the cropped image data to the setting.
		 *
		 * @param {Object} croppedImage Cropped attachment data.
		 */
		onCropped( croppedImage ) {
			const url = croppedImage.url,
				attachmentId = croppedImage.attachment_id,
				width = croppedImage.width,
				height = croppedImage.height;
			this.setImageFromURL( url, attachmentId, width, height );
		}

		/**
		 * If cropping was skipped, apply the image data directly to the setting.
		 *
		 * @param {Object} selection
		 */
		onSkippedCrop( selection ) {
			const url = selection.get( 'url' ),
				width = selection.get( 'width' ),
				height = selection.get( 'height' );
			this.setImageFromURL( url, selection.id, width, height );
		}

		/**
		 * Creates a new wp.customize.HeaderTool.ImageModel from provided
		 * header image data and inserts it into the user-uploaded headers
		 * collection.
		 *
		 * @param {string} url
		 * @param {number} attachmentId
		 * @param {number} width
		 * @param {number} height
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

			onSelect( data );
			dispatch( 'core/editor' ).editPost( { featured_media: attachmentId } );
		}
	};
};
