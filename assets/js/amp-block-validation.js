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
		 * The blocks with validation errors.
		 */
		blocksWithErrors: {},

		/**
		 * Boot module.
		 *
		 * @param {Object} data - Module data.
		 * @return {void}
		 */
		boot: function boot( data ) {
			module.data = data;
			wp.data.subscribe( function() {

				// @todo Limit to subscribing to changes to validationErrors.
				// @todo Changes to the block errors needs to set data?
				module.blocksWithErrors = module.getBlocksWithErrors(); // @todo Why not call this in conditionallyAddNotice?
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
		 * @param {Function} BlockEdit - The original edit() method of the block.
		 * @returns {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
		 */
		conditionallyAddNotice: function conditionallyAddNotice( BlockEdit ) {
			return function( props ) {
				var errors, result;
				errors = module.getBlockValidationErrors( props );
				result = [
					wp.element.createElement( BlockEdit, _.extend( {}, props, { key: 'amp-original-edit' } ) )
				];

				if ( errors.length > 0 ) {
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
		 *
		 * @returns {Object} The blocks with errors.
		 */
		getBlocksWithErrors: function getBlocksWithErrors() {
			var currentPost      = wp.data.select( 'core/editor' ).getCurrentPost(),
				blocksWithErrors = {};

			if ( ! Array.isArray( currentPost[ module.data.restValidationErrorsField ] ) ) {
				return blocksWithErrors;
			}

			// jscs:disable requireCamelCaseOrUpperCaseIdentifiers
			currentPost[ module.data.restValidationErrorsField ].forEach( function( validationError ) {
				if ( ! validationError.sources ) {
					return;
				}
				validationError.sources.forEach( function( source ) { // @todo Only use the leaf block source. Ignore parent blocks for validation errors.
					var blockValidationError;
					if ( ! source.block_name ) {
						return;
					}
					blockValidationError = _.extend( {}, validationError );
					if ( source.block_attrs ) {
						blockValidationError.blockAttrs = source.block_attrs;
					}

					if ( blocksWithErrors[ source.block_name ] ) {
						blocksWithErrors[ source.block_name ].push( blockValidationError );
					} else {
						blocksWithErrors[ source.block_name ] = [ blockValidationError ];
					}
				} );
			} );

			// jscs:enable requireCamelCaseOrUpperCaseIdentifiers
			return blocksWithErrors;
		},

		/**
		 * Gets the validation errors for a specific block if they exist.
		 *
		 * In module.blocksWithErrors, the errors are stored by block name.
		 * This finds the validation errors for a specific block, based on its attributes or content.
		 *
		 * @todo: keep refining how this finds if the errors match.
		 * @param {Object} props - Properties for the block.
		 * @return {Array} The validation error(s) for the block, or an empty array.
		 */
		getBlockValidationErrors: function getBlockValidationErrors( props ) {
			var rawErrors,
				validationErrors = [];

			if ( ! module.blocksWithErrors[ props.name ] ) {
				return validationErrors;
			}

			rawErrors = module.blocksWithErrors[ props.name ];
			rawErrors.forEach( function( validationError ) { // @todo Switch to using _.filter().

				// Uses _.isMatch because the props attributes can also have default attributes that blockAttrs doesn't have.
				if ( validationError.blockAttrs && _.isMatch( props.attributes, validationError.blockAttrs ) ) {
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
		 * @todo This doesn't seem to be working.
		 * @param {Object} validationError - The validation errors to check.
		 * @param {Object} propAttributes  - The block attributes, originally passed in the props object.
		 * @returns {Boolean} Whether node_name and the node_attributes are in the block.
		 */
		doNameAndAttributesMatch: function doNameAndAttributesMatch( validationError, propAttributes ) {
			var attribute, attributes,
				attributesKey = module.getAttributesKey( validationError );
			if ( ! attributesKey || ! propAttributes.hasOwnProperty( 'content' ) || ! propAttributes.content.includes( validationError.node_name ) ) { // jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
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
} )();
