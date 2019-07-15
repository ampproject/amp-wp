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
import { enforceFileSize, enforceFileType, getNoticeTemplate, mediaLibraryHasTwoNotices } from '../helpers';

const { wp } = window;

const NOTICE_CLASSNAME = 'notice notice-warning notice-alt inline';

/**
 * FeaturedImageSelectionError
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */
const FeaturedImageSelectionError = wp.media.View.extend( {
	className: NOTICE_CLASSNAME,
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
 * SelectionFileTypeError
 *
 * Applies if the featured image has the wrong file type, like .mov or .txt.
 * Very similar to the FeaturedImageSelectionError class.
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */
export const SelectionFileTypeError = wp.media.View.extend( {
	className: 'notice notice-warning notice-alt inline',
	template: ( () => {
		const message = sprintf(
			/* translators: 1: the selected file type. */
			__( 'The selected file mime type, %1$s, is not allowed.', 'amp' ),
			'{{mimeType}}',
		);

		return getNoticeTemplate( message );
	} )(),
} );

/**
 * SelectionFileSizeError
 *
 * Applies when the video size is more than a certain amount of MB per second.
 * Very similar to the FeaturedImageSelectionError class.
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */
export const SelectionFileSizeError = wp.media.View.extend( {
	className: NOTICE_CLASSNAME,
	template: ( () => {
		const message = sprintf(
			/* translators: 1: the recommended max MB per second for videos. 2: the actual MB per second of the video. */
			__( 'A video size of less than %1$s MB per second is recommended. The selected video is %2$s MB per second.', 'amp' ),
			'{{maxVideoMegabytesPerSecond}}',
			'{{actualVideoMegabytesPerSecond}}',
		);

		return getNoticeTemplate( message );
	} )(),
} );

/**
 * FeaturedImageToolbarSelect
 *
 * Prevent selection of an image that does not meet the minimum requirements.
 * Also enforces the file type, ensuring that it was in the allowedTypes prop.
 *
 * @class
 * @augments wp.media.view.Toolbar.Select
 * @augments wp.media.view.Toolbar
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 * @inheritDoc
 */
export const FeaturedImageToolbarSelect = wp.media.view.Toolbar.Select.extend( {
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

		if (
			! attachment ||
			'image' !== attachment.get( 'type' ) ||
			! attachment.get( 'width' ) ||
			( attachment.get( 'width' ) >= minWidth && attachment.get( 'height' ) >= minHeight )
		) {
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

		enforceFileType.call( this, attachment, SelectionFileTypeError );
	},
} );

/**
 * EnforcedFileToolbarSelect
 *
 * Prevents selecting an attachment that has the wrong file type, like .mov or .txt.
 *
 * @class
 * @augments wp.media.view.Toolbar.Select
 * @augments wp.media.view.Toolbar
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 * @inheritDoc
 */
export const EnforcedFileToolbarSelect = wp.media.view.Toolbar.Select.extend( {
	/**
	 * Refresh the view.
	 */
	refresh() {
		wp.media.view.Toolbar.Select.prototype.refresh.call( this );

		const state = this.controller.state();
		const selection = state.get( 'selection' );
		const attachment = selection.models[ 0 ];

		enforceFileType.call( this, attachment, SelectionFileTypeError );
		enforceFileSize.call( this, attachment, SelectionFileSizeError );

		// If there are two notices, like for wrong size and type, prevent the notices from covering the media.
		const mediaFrame = this.$el.parents( '.media-frame' );
		if ( mediaFrame ) {
			mediaFrame.toggleClass( 'has-two-notices', mediaLibraryHasTwoNotices.call( this ) );
		}
	},
} );

/**
 * Gets the select media frame, which displays in the bottom of the Media Library.
 *
 * @param {Class} ToolbarSelect The select toolbar that display at the bottom of the Media Library.
 * @return {Class} ToolbarSelect A wp.media Class that creates a Media Library toolbar.
 */
export const getSelectMediaFrame = ( ToolbarSelect ) => {
	/**
	 * Selects a featured image from the media library.
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
	return wp.media.view.MediaFrame.Select.extend( {
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
			options = {
				...options,
				allowedTypes: get( this, [ 'options', 'allowedTypes' ], null ),
			};

			toolbar.view = new ToolbarSelect( options );
		},
	} );
};
