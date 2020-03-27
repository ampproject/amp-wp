/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
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
			const wpLibrary = wp.media.controller.Library;

			const Library = wpLibrary.extend( {
				defaults: {
					...wpLibrary.prototype.defaults,
					title: __( 'Choose image', 'amp' ),
					library: wp.media.query( { type: 'image' } ),
					multiple: false,
					date: false,
					priority: 20,
					// Note: These suggestions are shown in the media library image browser.
					suggestedWidth: EXPECTED_WIDTH,
					suggestedHeight: EXPECTED_HEIGHT,
				},

				activate() {
					this.updateSelection();
					this.frame.on( 'open', this.updateSelection, this );

					// eslint-disable-next-line prefer-rest-params
					wpLibrary.prototype.activate.apply( this, arguments );
				},

				deactivate() {
					this.frame.off( 'open', this.updateSelection, this );

					// eslint-disable-next-line prefer-rest-params
					wpLibrary.prototype.deactivate.apply( this, arguments );
				},

				updateSelection() {
					const selection = this.get('selection'),
						id = wp.media.view.settings.post.featuredImageId;
					let attachment;

					if ( '' !== id && -1 !== id ) {
						attachment = wp.media.model.Attachment.get( id );
						attachment.fetch();
					}

					selection.reset( attachment ? [ attachment ] : [] );
				}
			} );

			this.frame = new FeaturedImageSelectMediaFrame( {
				allowedTypes: this.props.allowedTypes,
				button: {
					text: __( 'Select', 'amp' ),
					close: false,
				},
				states: [ new Library() ],
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

			if ( ! wp.media.view.settings.post.featuredImageId ) {
				return;
			}

			wp.media.featuredImage.set( attachment ? attachment.id : -1 );

			this.frame.close();
		}
	};
};
