/**
 * External dependencies
 */
import { template } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

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

		const errorTemplate = template(
			`<p>${ message }</p>`,
			{
				evaluate: /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape: /\{\{([^\}]+?)\}\}(?!\})/g,
			}
		);
		return ( data ) => {
			return errorTemplate( data );
		};
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

		if ( ! attachment || ( attachment.get( 'width' ) >= minWidth && attachment.get( 'height' ) >= minHeight ) ) {
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

		toolbar.view = new FeaturedImageToolbarSelect( options );
	},
} );

export default FeaturedImageSelectMediaFrame;
