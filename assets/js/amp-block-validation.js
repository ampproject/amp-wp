/**
 * AMP Gutenberg integration.
 *
 * On editing a block, this checks that the content is AMP-compatible.
 * And it displays a notice if it's not.
 */

/* exported ampBlockValidation */
var ampBlockValidation = ( function() {
	'use strict';

	var module = {
		/**
		 * Holds data.
		 */
		data: {},

		/**
		 * Boot module.
		 *
		 * @param {Object} data Object data.
		 * @return {void}
		 */
		boot: function( data ) {
			module.data = data;
			wp.hooks.addFilter(
				'blocks.BlockEdit',
				'amp/add-notice',
				module.conditionallyAddNotice
			);
		},

		/**
		 * Wraps the edit() method of a block, and conditionally adds a Notice.
		 *
		 * @param {Function} OriginalBlockEdit The original edit() method of the block.
		 * @returns {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
		 */
		conditionallyAddNotice: function( OriginalBlockEdit ) {
			return function( props ) {
				var currentPost = wp.data.select( 'core/editor' ).getCurrentPost(),
					hasErrors   = currentPost.hasOwnProperty( module.data.errorKey ) && 0 < currentPost[ module.data.errorKey ].length,
					result      = [ wp.element.createElement( OriginalBlockEdit, props ) ];

				// @todo: find if the errors apply to this block, as this simply finds if there are any errors at all.
				// If the issue is caused by a block, the source looks to have a block_name.
				if ( hasErrors ) {
					result.push( wp.element.createElement(
						wp.components.Notice,
						{
							status: 'warning',
							content: module.data.i18n.notice.replace( '%s', props.name ),
							isDismissible: false
						}
					) );
				}
				return result;
			};
		}

	};
	return module;

} )();
