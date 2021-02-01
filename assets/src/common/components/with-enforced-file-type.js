/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { EnforcedFileToolbarSelect, getSelectMediaFrame } from './select-media-frame';

const { wp } = window;

/**
 * Gets a wrapped version of MediaUpload, to enforce that it has the correct file type.
 *
 * Only intended for the MediaUpload in the Core Video block.
 * Though this will also apply to any other MediaUpload with allowedTypes of [ 'video' ].
 * Partly copied from customize-controls.js.
 *
 * @param {Function} InitialMediaUpload The MediaUpload component, passed from the filter.
 * @return {Function} The wrapped component.
 */
export default ( InitialMediaUpload ) => {
	/**
	 * Partly copied from customize-controls.js.
	 *
	 * @see wp.media.HeaderControl
	 */
	return class EnforcedFileTypeMediaUpload extends InitialMediaUpload {
		/**
		 * Constructs the class.
		 *
		 * @param {*} args Constructor arguments.
		 */
		constructor( ...args ) {
			super( ...args );

			// This class should only be present when only 'video' types are allowed, like in the Core Video block.
			if ( isEqual( [ 'video/mp4' ], this.props.allowedTypes ) ) {
				this.initFileTypeMedia();
			}
		}

		/**
		 * Initialize.
		 *
		 * Mainly copied from customize-controls.js.
		 * Overwrites the Media Library frame, this.frame.
		 * And checks that the file type is correct.
		 *
		 * @see wp.media.CroppedImageControl.initFrame
		 */
		initFileTypeMedia = () => {
			const SelectMediaFrame = getSelectMediaFrame( EnforcedFileToolbarSelect );
			const previousOnSelect = this.onSelect;
			const isVideo = isEqual( [ 'video' ], this.props.allowedTypes );
			const queryType = isVideo ? 'video/mp4' : this.props.allowedTypes; // For the Video block, only display .mp4 files.
			this.frame = new SelectMediaFrame( {
				allowedTypes: this.props.allowedTypes,
				button: {
					text: __( 'Select', 'amp' ),
					close: false,
				},
				states: [
					new wp.media.controller.Library( {
						title: __( 'Select or Upload Media', 'amp' ),
						library: wp.media.query( { type: queryType } ),
						multiple: false,
						date: false,
						priority: 20,
					} ),
				],
			} );

			wp.media.frame = this.frame;
			this.frame.on( 'close', () => {
				this.initFileTypeMedia();
			}, this );

			this.frame.on( 'select', () => {
				if ( previousOnSelect ) {
					previousOnSelect();
				}
				this.frame.close();
			}, this );
		}
	};
};
