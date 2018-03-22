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
		 * The blocks with validation errors.
		 */
		blocksWithErrors: {},

		/**
		 * Boot module.
		 *
		 * @param {Object} data Object data.
		 * @return {void}
		 */
		boot: function( data ) {
			module.data = data;
			wp.data.subscribe( function() {
				module.blocksWithErrors = module.getBlocksWithErrors();
			} );
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
				var result = [ wp.element.createElement( OriginalBlockEdit, props ) ];

				// @todo: find if the errors apply to this specific block, not only the same type of block.
				if ( module.blocksWithErrors.hasOwnProperty( props.name ) ) {
					result.push( wp.element.createElement(
						wp.components.Notice,
						{
							status: 'warning',
							content: module.data.i18n.notice.replace( '%s', props.name ) + ' ' + module.blocksWithErrors[ props.name ][0].code,
							isDismissible: false
						}
					) );
				}
				return result;
			};
		},

		/**
		 * Gets the block types with errors.
		 *
		 * Iterates through the 'amp_validation_errors' from the REST API response.
		 * This returns an object, with block types as the keys, and error arrays as the values.
		 * The block's overriden edit() method can then get the errors for its block type.
		 *
		 * @returns {Object|null} The blocks with errors.
		 */
		getBlocksWithErrors: function() {
			var currentPost      = wp.data.select( 'core/editor' ).getCurrentPost(),
				blocksWithErrors = {};
			if ( ! currentPost.hasOwnProperty( module.data.errorKey ) || ! Array.isArray( currentPost[ module.data.errorKey ] ) ) {
				return blocksWithErrors;
			}

			currentPost[ module.data.errorKey ].forEach( function( validationError ) {
				if ( validationError.hasOwnProperty( 'sources' ) ) {
					validationError.sources.forEach( function( source ) {
						if ( source.hasOwnProperty( 'block_name' ) ) {
							if ( blocksWithErrors.hasOwnProperty( source.block_name ) ) {
								blocksWithErrors[ source.block_name ].push( validationError ); // jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
							} else {
								blocksWithErrors[ source.block_name ] = [ validationError ]; // jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
							}
						}
					} );
				}
			} );
			return blocksWithErrors;
		}

	};
	return module;

} )();
