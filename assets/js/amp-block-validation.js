/**
 * Validates blocks for AMP compatibility.
 *
 * This uses the REST API response from saving a page to find validation errors.
 * If one exists for a block, it display it inline with a Notice component.
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
				var errors = module.getBlockValidationErrors( props ),
					result = [ wp.element.createElement( OriginalBlockEdit, props ) ];

				if ( errors.length > 0 ) {
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
		 * The block's overridden edit() method can then get the errors for its block type.
		 *
		 * @returns {Object} The blocks with errors.
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
		},

		/**
		 * Gets the validation errors for a specific block if they exist.
		 *
		 * In module.blocksWithErrors, the errors are stored by block name.
		 * This finds the validation errors for a specific block, based on its content.
		 *
		 * @todo: search more specifically, as this currently only checks for matching of the node_name and node_attributes.
		 * @param {Object} props Properties for the block.
		 * @return {Array} The validation error(s) for the block, or an empty array.
		 */
		getBlockValidationErrors: function( props ) {
			var attributes, content, rawErrors,
				validationErrors = [];

			if ( ! props.attributes.hasOwnProperty( 'content' ) || ! module.blocksWithErrors.hasOwnProperty( props.name ) ) {
				return validationErrors;
			}
			content   = props.attributes.content;
			rawErrors = module.blocksWithErrors[ props.name ];

			rawErrors.forEach( function( validationError ) {
				if ( ! content.includes( validationError.node_name ) || ! validationError.hasOwnProperty( 'node_attributes' ) ) { // jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
					return;
				}
				attributes = validationError.node_attributes;
				for ( var attribute in attributes ) { // jscs:ignore requireCamelCaseOrUpperCaseIdentifiers

					// Check that the content has both the attribute and the property.
					if ( attributes.hasOwnProperty( attribute ) ) {
						if ( content.includes( attribute ) && content.includes( attributes[ attribute ] ) ) {
							validationErrors.push( validationError );
						}
					}
				}
			} );

			return validationErrors;
		}

	};
	return module;

} )();
