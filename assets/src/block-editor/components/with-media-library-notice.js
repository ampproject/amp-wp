/**
 * External dependencies
 */
import { isObject } from 'lodash';

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { FeaturedImageToolbarSelect, getSelectMediaFrame } from '../../common/components/select-media-frame';
import { setImageFromURL } from '../../common/helpers';

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
	if ( ! isObject( InitialMediaUpload ) ) {
		return InitialMediaUpload;
	}

	const { width: EXPECTED_WIDTH, height: EXPECTED_HEIGHT } = minImageDimensions;

	/**
	 * Mostly copied from customize-controls.js, with slight changes.
	 *
	 * @see https://github.com/WordPress/wordpress-develop/blob/c80325658f85d24ff82295dd2d55bfdf789f4163/src/js/_enqueues/wp/customize/controls.js#L4695
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
			} else {
				// Restore the original`onOpen` callback as it will be overridden by the parent class.
				this.frame.off( 'open', this.onOpen );
				this.frame.on( 'open', super.onOpen.bind( this ) );
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

			const FeaturedImageLibrary = wp.media.controller.FeaturedImage.extend( {
				defaults: {
					...wp.media.controller.FeaturedImage.prototype.defaults,
					date: false,
					filterable: false,
					// Note: These suggestions are shown in the media library image browser.
					suggestedWidth: EXPECTED_WIDTH,
					suggestedHeight: EXPECTED_HEIGHT,
				},
			} );

			this.frame = new FeaturedImageSelectMediaFrame( {
				allowedTypes: this.props.allowedTypes,
				state: 'featured-image',
				states: [ new FeaturedImageLibrary(), new wp.media.controller.EditImage() ],
			} );

			this.frame.on( 'toolbar:create:featured-image', function( toolbar ) {
				/**
				 * @this wp.media.view.MediaFrame.Select
				 */
				this.createSelectToolbar( toolbar, {
					text: wp.media.view.l10n.setFeaturedImage,
					state: this.options.state,
				} );
			}, this.frame );

			this.frame.on( 'open', this.onOpen );

			this.frame.state( 'featured-image' ).on( 'select', this.onSelectImage, this );

			// See wp.media() for this.
			wp.media.frame = this.frame;
		}

		/**
		 * Ensure the selected image is the first item in the collection.
		 *
		 * @see https://github.com/WordPress/gutenberg/blob/c58b32266f8c950c5b9927d286608343078aee02/packages/media-utils/src/components/media-upload/index.js#L401-L417
		 */
		onOpen() {
			const frameContent = this.frame.content.get();
			if ( frameContent && frameContent.collection ) {
				const collection = frameContent.collection;

				// Clean all attachments we have in memory.
				collection
					.toArray()
					.forEach( ( model ) => model.trigger( 'destroy', model ) );

				// Reset has more flag, if library had small amount of items all items may have been loaded before.
				collection.mirroring._hasMore = true;

				// Request items.
				collection.more();
			}
		}

		/**
		 * Handles image selection.
		 */
		onSelectImage() {
			const attachment = this.frame.state( 'featured-image' ).get( 'selection' ).first().toJSON();
			const dispatchImage = ( attachmentId ) => {
				dispatch( 'core/editor' ).editPost( { featured_media: attachmentId } );
			};
			const { onSelect } = this.props;
			const { url, id, width, height } = attachment;
			setImageFromURL( { url, id, width, height, onSelect, dispatchImage } );

			if ( ! wp.media.view.settings.post.featuredImageId ) {
				return;
			}

			wp.media.featuredImage.set( attachment ? attachment.id : -1 );
		}
	};
};
