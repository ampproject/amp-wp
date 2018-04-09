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
		data: {
			i18n: {
				invalidAmpContentNotice: ''
			}
		},

		/**
		 * Boot module.
		 *
		 * @param {Object} data - Module data.
		 * @return {void}
		 */
		boot: function boot( data ) {
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
		 * @param {Function} BlockEdit - The original edit() method of the block.
		 * @returns {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
		 */
		conditionallyAddNotice: function conditionallyAddNotice( BlockEdit ) {
			return function( props ) {
				var errors, result;

				errors = module.getBlockValidationErrors( props );
				result = [
					wp.element.createElement( BlockEdit, _.extend({}, props, { key: 'amp-original-edit' }) )
				];

				if ( 0 < errors.length ) {
					result.unshift(

						// @todo Add PanelBody with validation error details.
						wp.element.createElement(
							wp.components.Notice,
							{
								status: 'warning',
								isDismissible: false,
								key: 'amp-validation-notice'
							},
							module.data.i18n.invalidAmpContentNotice + ' ' + _.unique( _.pluck( errors, 'code' ) ).join( ', ' )
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
		 * This finds the validation errors for a specific block, based on its attributes or content.
		 * @todo: keep refining how this finds if the errors match.
		 *
		 * @param {Object} props            - Properties for the block.
		 * @param {string} props.name       - Block name.
		 * @param {string} props.attributes - Block attributes.
		 * @return {Array} The validation error(s) for the block, or an empty array.
		 */
		getBlockValidationErrors: function getBlockValidationErrors( props ) {
			var allValidationErrors,
				blockValidationErrors = [];

			allValidationErrors = wp.data.select( 'core/editor' ).getCurrentPost()[ module.data.restValidationErrorsField ];
			if ( ! Array.isArray( allValidationErrors ) ) {
				return blockValidationErrors;
			}

			allValidationErrors.forEach( function( validationError ) {
				var i, source;
				if ( ! validationError.sources ) {
					return;
				}

				// Find the inner-most nested block source only; ignore any nested blocks.
				for ( i = validationError.sources.length - 1; 0 <= i; i-- ) {
					source = validationError.sources[ i ];

					if ( ! source.block_name || source.block_name !== props.name ) {
						continue;
					}

					// Uses _.isMatch because the props attributes can also have default attributes that blockAttrs doesn't have.
					if ( source.block_attrs && _.isMatch( props.attributes, source.block_attrs ) || module.doNameAndAttributesMatch( validationError, props.attributes ) ) {
						blockValidationErrors.push( validationError );
						break;
					}
				}
			});

			return blockValidationErrors;
		},

		/**
		 * Whether the node_name and node_attributes in the validation error are present in the block.
		 *
		 * @todo This doesn't seem to be working.
		 * @param {Object} validationError - The validation errors to check.
		 * @param {Object} propAttributes  - The block attributes, originally passed in the props object.
		 * @returns {Boolean} Whether node_name and the node_attributes are in the block.
		 */
		doNameAndAttributesMatch: function doNameAndAttributesMatch( validationError, propAttributes ) {
			var attribute, attributes,
				attributesKey = module.getAttributesKey( validationError );
			if ( ! attributesKey || ! propAttributes.hasOwnProperty( 'content' ) || ! propAttributes.content.includes( validationError.node_name ) ) {
				return false;
			}

			// Ensure the content has all attributes and properties in the validationError.
			attributes = validationError[ attributesKey ];
			for ( attribute in attributes ) {
				if ( ! attributes.hasOwnProperty( attribute ) || ! propAttributes.content.includes( attribute ) || ! propAttributes.content.includes( attributes[ attribute ]) ) {
					return false;
				}
			}
			return true;
		},

		/**
		 * Gets the key for the attributes in validationError.
		 *
		 * @param {Object} validationError - The validation errors to check.
		 * @returns {String|null} attributeKey The key used to get the attributes, or null.
		 */
		getAttributesKey: function getAttributesKey( validationError ) {
			if ( validationError.hasOwnProperty( 'node_attributes' ) ) {
				return 'node_attributes';
			} else if ( validationError.hasOwnProperty( 'element_attributes' ) ) {
				return 'element_attributes';
			} else {
				return null;
			}
		}

	};

	return module;
}() );
