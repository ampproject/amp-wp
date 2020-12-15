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
import { enforceFileType, getNoticeTemplate, hasMinimumDimensions } from '../helpers';

const { wp } = window;

const NOTICE_CLASSNAME = 'notice notice-warning notice-alt inline';

/**
 * FeaturedImageSelectionError
 *
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */
const FeaturedImageSelectionError = wp.media.View.extend( {
	className: NOTICE_CLASSNAME,
	template: ( () => {
		let message = sprintf(
			/* translators: 1: image width in pixels. 2: image height in pixels. */
			__( 'The selected image is too small (%1$s by %2$s pixels).', 'amp' ),
			'{{width}}',
			'{{height}}',
			'{{minWidth}}',
			'{{minHeight}}',
		);

		message += ' <# if ( minWidth && minHeight ) { #>';
		message += sprintf(
			/* translators: 1: required minimum width in pixels. 2: required minimum height in pixels. */
			__( 'It should have a size of at least %1$s by %2$s pixels.', 'amp' ),
			'{{minWidth}}',
			'{{minHeight}}',
		);
		message += '<# } else if ( minWidth ) { #>';
		message += sprintf(
			/* translators: placeholder is required minimum width in pixels. */
			__( 'It should have a width of at least %s pixels.', 'amp' ),
			'{{minWidth}}',
		);
		message += '<# } else if ( minHeight ) { #>';
		message += sprintf(
			/* translators: placeholder is required minimum height in pixels. */
			__( 'It should have a height of at least %s pixels.', 'amp' ),
			'{{minHeight}}',
		);
		message += '<# } #>';

		return getNoticeTemplate( message );
	} )(),
} );

/**
 * SelectionFileTypeError
 *
 * Applies if the featured image has the wrong file type, like .mov or .txt.
 * Very similar to the FeaturedImageSelectionError class.
 *
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
 * @augments wp.media.view.Toolbar.Select
 * @augments wp.media.view.Toolbar
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
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
		const minWidth = state.collection.get( 'featured-image' ).get( 'suggestedWidth' );
		const minHeight = state.collection.get( 'featured-image' ).get( 'suggestedHeight' );

		if (
			! attachment ||
			'image' !== attachment.get( 'type' ) ||
			! attachment.get( 'width' ) ||
			hasMinimumDimensions(
				{
					width: attachment.get( 'width' ),
					height: attachment.get( 'height' ),
				},
				{
					width: minWidth,
					height: minHeight,
				},
			)
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
				} ),
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
 * @augments wp.media.view.Toolbar.Select
 * @augments wp.media.view.Toolbar
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
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
	},
} );

/**
 * Gets the select media frame, which displays in the bottom of the Media Library.
 *
 * @param {Object} ToolbarSelect The select toolbar that display at the bottom of the Media Library.
 * @return {Object} ToolbarSelect A wp.media Class that creates a Media Library toolbar.
 */
export const getSelectMediaFrame = ( ToolbarSelect ) => {
	/**
	 * Selects a featured image from the media library.
	 *
	 * @augments wp.media.view.MediaFrame.Select
	 * @augments wp.media.view.MediaFrame
	 * @augments wp.media.view.Frame
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 * @mixes wp.media.controller.StateMachine
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
