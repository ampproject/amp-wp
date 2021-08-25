/**
 * External dependencies
 */
import { get, now, template } from 'lodash';
import { featuredImageMinimumWidth, featuredImageMinimumHeight } from 'amp-block-editor-data';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	FILE_TYPE_ERROR_VIEW,
} from '../constants';

/**
 * Determines whether whether the image has the minimum required dimensions.
 *
 * The image should have a width of at least 1200 pixels to satisfy the requirements of Google Search for Schema.org metadata.
 *
 * @param {Object} media             A media object with width and height values.
 * @param {number} media.width       Media width in pixels.
 * @param {number} media.height      Media height in pixels.
 * @param {Object} dimensions        An object with minimum required width and height values.
 * @param {number} dimensions.width  Required media width in pixels.
 * @param {number} dimensions.height Required media height in pixels.
 * @return {boolean} Whether the media has the minimum dimensions.
 */
export const hasMinimumDimensions = ( media, dimensions ) => {
	if ( ! media || ! media.width || ! media.height ) {
		return false;
	}

	const { width, height } = dimensions;

	return (
		( ! width || media.width >= width ) &&
		( ! height || media.height >= height )
	);
};

/**
 * Get minimum dimensions for a featured image.
 *
 * "Images should be at least 1200 pixels wide.
 * For best results, provide multiple high-resolution images (minimum of 800,000 pixels when multiplying width and height)
 * with the following aspect ratios: 16x9, 4x3, and 1x1."
 *
 * Given this requirement, this function ensures the right aspect ratio.
 * The 16/9 aspect ratio is chosen because it has the smallest height for the given width.
 *
 * @see https://developers.google.com/search/docs/data-types/article#article_types
 * @return {Object} Minimum dimensions including width and height.
 */
export const getMinimumFeaturedImageDimensions = () => {
	return {
		width: featuredImageMinimumWidth,
		height: featuredImageMinimumHeight,
	};
};

/**
 * Validates the an image based on requirements.
 *
 * @param {Object}  media                      A media object.
 * @param {string}  media.mime_type            The media item's mime type.
 * @param {Object}  media.media_details        A media details object with width and height values.
 * @param {number}  media.media_details.width  Media width in pixels.
 * @param {number}  media.media_details.height Media height in pixels.
 * @param {Object}  dimensions                 An object with minimum required width and height values.
 * @param {number}  dimensions.width           Minimum required width value.
 * @param {number}  dimensions.height          Minimum required height value.
 * @param {boolean} required                   Whether the image is required or not.
 * @return {string[]|null} Validation errors, or null if there were no errors.
 */
export const validateFeaturedImage = ( media, dimensions, required ) => {
	if ( ! media ) {
		if ( required ) {
			return [ __( 'Selecting a featured image is required.', 'amp' ) ];
		}

		return [ __( 'Selecting a featured image is recommended for an optimal user experience.', 'amp' ) ];
	}

	const errors = [];

	if ( ! [ 'image/png', 'image/gif', 'image/jpeg', 'image/webp', 'image/svg+xml' ].includes( media.mime_type ) ) {
		errors.push(
			/* translators: 1: JPEG, 2: PNG. 3: GIF, 4: WebP, 5: SVG */
			sprintf( __( 'The featured image must be of either %1$s, %2$s, %3$s, %4$s, or %5$s format.', 'amp' ), 'JPEG', 'PNG', 'GIF', 'WebP', 'SVG' ),
		);
	}

	if ( ! hasMinimumDimensions( media.media_details, dimensions ) ) {
		const { width, height } = dimensions;

		if ( width && height ) {
			errors.push(
				/* translators: 1: minimum width, 2: minimum height. */
				sprintf( __( 'The featured image should have a size of at least %1$s by %2$s pixels.', 'amp' ), Math.ceil( width ), Math.ceil( height ) ),
			);
		} else if ( dimensions.width ) {
			errors.push(
				/* translators: placeholder is minimum width. */
				sprintf( __( 'The featured image should have a width of at least %s pixels.', 'amp' ), Math.ceil( width ) ),
			);
		} else if ( dimensions.height ) {
			errors.push(
				/* translators: placeholder is minimum height. */
				sprintf( __( 'The featured image should have a height of at least %s pixels.', 'amp' ), Math.ceil( height ) ),
			);
		}
	}

	return 0 === errors.length ? null : errors;
};

/**
 * Gets the compiled template for a given notice message.
 *
 * @param {string} message The message to display in the template.
 * @return {Function} compiledTemplate A function accepting the data, which creates a compiled template.
 */
export const getNoticeTemplate = ( message ) => {
	const errorTemplate = template(
		`<p>${ message }</p>`,
		{
			evaluate: /<#([\s\S]+?)#>/g,
			interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
			escape: /\{\{([^\}]+?)\}\}(?!\})/g,
		},
	);

	return ( data ) => {
		return errorTemplate( data );
	};
};

/**
 * Gets whether the file type is allowed.
 *
 * For videos, only supported mime types as defined by the editor settings should be allowed.
 * But the allowedTypes property only has 'video', and it can accidentally allow mime types that are not supported.
 * So this returns false for videos with mime types other than the ones in the editor settings.
 *
 * @param {Object} attachment   The file to evaluate.
 * @param {Array}  allowedTypes The allowed file types.
 * @return {boolean} Whether the file type is allowed.
 */
export const isFileTypeAllowed = ( attachment, allowedTypes ) => {
	const fileType = attachment.get( 'type' );
	const mimeType = attachment.get( 'mime' );

	if ( ! allowedTypes.includes( fileType ) && ! allowedTypes.includes( mimeType ) ) {
		return false;
	}

	return 'video' !== fileType;
};

/**
 * If the attachment has the wrong file type, this displays a notice in the Media Library and disables the 'Select' button.
 *
 * This is not an arrow function so that it can be called with enforceFileType.call( this, foo, bar ).
 *
 * @param {Object} attachment     The selected attachment.
 * @param {Object} SelectionError The error to display.
 */
export const enforceFileType = function( attachment, SelectionError ) {
	if ( ! attachment ) {
		return;
	}

	const allowedTypes = get( this, [ 'options', 'allowedTypes' ], null );
	const selectButton = this.get( 'select' );

	// If the file type isn't allowed, display a notice and disable the 'Select' button.
	if ( allowedTypes && attachment.get( 'type' ) && ! isFileTypeAllowed( attachment, allowedTypes ) ) {
		this.secondary.set(
			FILE_TYPE_ERROR_VIEW,
			new SelectionError( { mimeType: attachment.get( 'mime' ) } ),
		);
		if ( selectButton && selectButton.model ) {
			selectButton.model.set( 'disabled', true ); // Disable the button to select the file.
		}
	} else {
		this.secondary.unset( FILE_TYPE_ERROR_VIEW );
		if ( selectButton && selectButton.model ) {
			selectButton.model.set( 'disabled', false ); // Enable the button to select the file.
		}
	}
};

/**
 * Sets the featured image, on selecting it in the Media Library.
 *
 * @param {Object}   args               Arguments.
 * @param {string}   args.url           Image URL.
 * @param {number}   args.id            Attachment ID.
 * @param {number}   args.width         Image width.
 * @param {number}   args.height        Image height.
 * @param {Function} args.onSelect      A function in the MediaUpload component called on selecting the image.
 * @param {Function} args.dispatchImage A function to dispatch the change in image to the store.
 */
export const setImageFromURL = ( { url, id, width, height, onSelect, dispatchImage } ) => {
	const data = {};
	data.url = url;
	data.thumbnail_url = url;
	data.timestamp = now();

	if ( id ) {
		data.attachment_id = id;
	}

	if ( width ) {
		data.width = width;
	}

	if ( height ) {
		data.height = height;
	}

	onSelect( data ); // @todo Does this do anything?
	dispatchImage( id );
};
