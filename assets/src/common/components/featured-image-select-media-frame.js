/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getNoticeTemplate } from '../helpers';

const { wp } = window;

/**
 * FeaturedImageSelectionError
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */
const FeaturedImageSelectionError = wp.media.View.extend( {
	className: 'notice notice-warning notice-alt inline',
	template: ( () => {
		const message = sprintf(
			/* translators: 1: image width in pixels. 2: image height in pixels. 3: required minimum width in pixels. 4: required minimum height in pixels. */
			__( 'The selected image is too small (%1$s by %2$s pixels). It should have a size of at least %3$s by %4$s pixels.', 'amp' ),
			'{{width}}',
			'{{height}}',
			'{{minWidth}}',
			'{{minHeight}}',
		);

		return getNoticeTemplate( message );
	} )(),
} );

/**
 * FeaturedImageSelectionFileTypeError
 *
 * Applies if the featured image has the wrong file type, like .mov or .txt.
 * Very similar to the FeaturedImageSelectionError class.
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */
const FeaturedImageSelectionFileTypeError = wp.media.View.extend( {
	className: 'notice notice-warning notice-alt inline',
	template: ( () => {
		const message = sprintf(
			/* translators: 1: the selected file type. */
			__( 'The selected file type, %1$s, is not allowed.', 'amp' ),
			'{{fileType}}',
		);

		return getNoticeTemplate( message );
	} )(),
} );

/**
 * FeaturedImageToolbarSelect
 *
 * Prevent selection of an image that does not meet the minimum requirements.
 *
 * @class
 * @augments wp.media.view.Toolbar.Select
 * @augments wp.media.view.Toolbar
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 * @inheritDoc
 */
const FeaturedImageToolbarSelect = wp.media.view.Toolbar.Select.extend( {

	/**
	 * Refresh the view.
	 */
	refresh() {
		wp.media.view.Toolbar.Select.prototype.refresh.call( this );

		const state = this.controller.state();
		const selection = state.get( 'selection' );

		const attachment = selection.models[ 0 ];
		const minWidth = state.collection.get( 'library' ).get( 'suggestedWidth' );
		const minHeight = state.collection.get( 'library' ).get( 'suggestedHeight' );
		const fileTypeError = 'select-file-type-error';

		if ( ! attachment || ! attachment.get( 'width' ) || ( attachment.get( 'width' ) >= minWidth && attachment.get( 'height' ) >= minHeight ) ) {
			this.secondary.unset( 'select-error' );
		} else {
			this.secondary.set(
				'select-error',
				new FeaturedImageSelectionError( {
					minWidth,
					minHeight,
					width: attachment.get( 'width' ),
					height: attachment.get( 'height' ),
				} )
			);
		}

		const fileType = attachment ? attachment.get( 'type' ) : null;
		const allowedTypes = get( this, [ 'options', 'allowedTypes' ], null );
		const select = this.get( 'select' );

		// If the file type isn't allowed, display a notice and disable the 'Select' button.
		if ( ! fileType || ! allowedTypes || allowedTypes.indexOf( fileType ) > -1 ) {
			this.secondary.unset( fileTypeError );
			if ( select && select.model ) {
				select.model.set( 'disabled', false ); // Enable the button to select the file.
			}
		} else {
			this.secondary.set(
				fileTypeError,
				new FeaturedImageSelectionFileTypeError( { fileType } )
			);
			if ( select && select.model ) {
				select.model.set( 'disabled', true ); // Disable the button to select the file.
			}
		}
	},
} );

/**
 * FeaturedImageSelectMediaFrame
 *
 * Select a featured image from the media library.
 *
 * @class
 * @augments wp.media.view.MediaFrame.Select
 * @augments wp.media.view.MediaFrame
 * @augments wp.media.view.Frame
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 * @mixes wp.media.controller.StateMachine
 * @inheritDoc
 */
const FeaturedImageSelectMediaFrame = wp.media.view.MediaFrame.Select.extend( {

	/**
	 * Create select toolbar.
	 *
	 * The only reason for this method is to override the select toolbar view class.
	 *
	 * @param {Object} toolbar
	 * @param {Object} [options={}]
	 * @this wp.media.controller.Region
	 */
	createSelectToolbar( toolbar, options ) {
		options = options || this.options.button || {};
		options.controller = this;
		options = Object.assign( {}, options, { allowedTypes: get( this, [ 'options', 'allowedTypes' ], null ) } );

		toolbar.view = new FeaturedImageToolbarSelect( options );
	},
} );

export default FeaturedImageSelectMediaFrame;
