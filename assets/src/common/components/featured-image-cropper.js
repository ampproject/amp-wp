
const { wp } = window;

/**
 * A state for cropping a featured image.
 *
 * @constructs FeaturedImageCropper
 * @inheritDoc
 */
const FeaturedImageCropper = wp.media.controller.Cropper.extend( {
	/**
	 * Creates an object with the image attachment and crop properties.
	 *
	 * @param {wp.media.model.Attachment} attachment The attachment to crop.
	 * @return {jQuery.promise} A jQuery promise that represents the crop image request.
	 */
	doCrop( attachment ) {
		const cropDetails = attachment.get( 'cropDetails' );
		const imgSelectOptions = this.imgSelect.getOptions();

		// Account for imprecise cropping at minWidth/minHeight by snapping to minimum dimension if within 10 pixels.
		if ( Math.abs( cropDetails.width - imgSelectOptions.minWidth ) < 10 ) {
			cropDetails.width = imgSelectOptions.minWidth;
		}
		if ( Math.abs( cropDetails.height - imgSelectOptions.minHeight ) < 10 ) {
			cropDetails.height = imgSelectOptions.minHeight;
		}

		// Force the resulting image to have the expected dimensions (scale down).
		cropDetails.dst_width = cropDetails.width;
		cropDetails.dst_height = cropDetails.height;

		return wp.ajax.post( 'crop-image', {
			nonce: attachment.get( 'nonces' ).edit,
			id: attachment.get( 'id' ),
			context: 'featured-image',
			cropDetails,
		} );
	},
} );

export default FeaturedImageCropper;
