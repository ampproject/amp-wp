/**
 * Validates blocks for AMP compatibility.
 *
 * This uses the REST API response from saving a page to find validation errors.
 * If one exists for a block, it display it inline with a Notice component.
 */

/* exported ampBlockValidation */
/* global wp, _ */
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
		 * @param {Object} data Module data.
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
					result.unshift(
						wp.element.createElement(
							wp.components.Notice,
							{
								status: 'warning',
								content: module.data.i18n.notice.replace( '%s', props.name ) + ' ' + module.getErrorCodes( errors ),
								isDismissible: false
							}
						),
						wp.element.createElement(
							wp.components.ExternalLink,
							{
								href: module.data.moreDetailsLink,
								children: module.data.i18n.moreDetails,
								className: 'notice notice-alt notice-warning'
							}
						)
					);
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
							if ( source.hasOwnProperty( 'block_attrs' ) ) {
								validationError.blockAttrs = source.block_attrs; // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
							}
							if ( blocksWithErrors.hasOwnProperty( source.block_name ) ) {
								blocksWithErrors[ source.block_name ].push( validationError );
							} else {
								blocksWithErrors[ source.block_name ] = [ validationError ];
							} // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
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
		 * This finds the validation errors for a specific block, based on its attributes or content.
		 *
		 * @todo: keep refining how this finds if the errors match.
		 * @param {Object} props Properties for the block.
		 * @return {Array} The validation error(s) for the block, or an empty array.
		 */
		getBlockValidationErrors: function( props ) {
			var rawErrors,
				validationErrors = [];

			if ( ! module.blocksWithErrors.hasOwnProperty( props.name ) ) {
				return validationErrors;
			}
			rawErrors = module.blocksWithErrors[ props.name ];

			rawErrors.forEach( function( validationError ) {

				// Uses _.isMatch because the props attributes can also have default attributes that blockAttrs doesn't have.
				if ( validationError.hasOwnProperty( 'blockAttrs' ) && _.isMatch( props.attributes, validationError.blockAttrs ) ) {
					validationErrors.push( validationError );
				} else if ( module.doNameAndAttributesMatch( validationError, props.attributes ) ) {
					validationErrors.push( validationError );
				}
			} );

			return validationErrors;
		},

		/**
		 * Whether the node_name and node_attributes in the validation error are present in the block.
		 *
		 * @param {Object} validationError The validation errors to check.
		 * @param {Object} propAttributes  The block attributes, originally passed in the props object.
		 * @returns {Boolean} Whether node_name and the node_attributes are in the block.
		 */
		doNameAndAttributesMatch: function( validationError, propAttributes ) {
			var attribute, attributes, attributesKey;
			if ( ! propAttributes.hasOwnProperty( 'content' ) || ! propAttributes.content.includes( validationError.node_name ) ) { // jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
				return false;
			}

			if ( validationError.hasOwnProperty( 'node_attributes' ) ) {
				attributesKey = 'node_attributes';
			} else if ( validationError.hasOwnProperty( 'element_attributes' ) ) {
				attributesKey = 'element_attributes';
			} else {
				return false;
			}

			// Ensure the content has all attributes and properties in the validationError.
			attributes = validationError[ attributesKey ];
			for ( attribute in attributes ) {
				if ( ! attributes.hasOwnProperty( attribute ) || ! propAttributes.content.includes( attribute ) || ! propAttributes.content.includes( attributes[ attribute ] ) ) {
					return false;
				}
			}
			return true;
		},

		/**
		 * Gets the unique error codes from the block errors.
		 *
		 * @param {Array} errors The validation errors for a block.
		 * @returns {String} errorCodes A comma-separated string of validation error codes.
		 */
		getErrorCodes: function( errors ) {
			var allErrors = [];
			errors.forEach( function( validationError ) {
				if ( ! allErrors.includes( validationError.code ) ) {
					allErrors.push( validationError.code  );
				}
			} );
			return allErrors.join( ', ' );
		}

	};

	return module;
} )();
