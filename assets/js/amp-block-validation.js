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
			var lastValidationErrors;
			module.data = data;

			wp.hooks.addFilter(
				'blocks.BlockEdit',
				'amp/add-notice',
				module.conditionallyAddNotice
			);

			module.store = wp.data.registerStore( 'amp/blockValidation', {
				reducer: function( _state, action ) {
					var state = _state || {
						blocksValidationErrors: {}
					};

					switch ( action.type ) {
						case 'UPDATE_BLOCKS_VALIDATION_ERRORS':
							return _.extend( {}, state, {
								blocksValidationErrors: action.blocksValidationErrors
							} );
						default:
							return state;
					}
				},
				actions: {
					updateBlocksValidationErrors: function( blocksValidationErrors ) {
						return {
							type: 'UPDATE_BLOCKS_VALIDATION_ERRORS',
							blocksValidationErrors: blocksValidationErrors
						};
					}
				},
				selectors: {
					getBlockValidationErrors: function( state, uid ) {
						return state.blocksValidationErrors[ uid ] || [];
					}
				}
			} );

			wp.data.subscribe( function() {
				var thisValidationErrors = wp.data.select( 'core/editor' ).getCurrentPost()[ module.data.restValidationErrorsField ];
				if ( thisValidationErrors && ! _.isEqual( lastValidationErrors, thisValidationErrors ) ) {
					lastValidationErrors = thisValidationErrors;
					module.updateBlocksValidationErrors( thisValidationErrors );
				}

				// @todo wp.data.dispatch( 'core/editor' ).createNotice()?
			} );
		},

		/**
		 * Update blocks' validation errors in the store.
		 *
		 * @param {Object[]} validationErrors - Validation errors.
		 * @return {void}
		 */
		updateBlocksValidationErrors: function updateBlocksValidationErrors( validationErrors ) {
			var blocksValidationErrors, blockOrder, currentPost, selectors;
			selectors = wp.data.select( 'core/editor' );
			blockOrder = selectors.getBlockOrder();
			currentPost = selectors.getCurrentPost();

			blocksValidationErrors = {};
			_.each( blockOrder, function( uid ) {
				blocksValidationErrors[ uid ] = [];
			} );

			_.each( validationErrors, function( validationError ) {
				var i, source, matchedBlockUid;
				if ( ! validationError.sources ) {
					return;
				}

				// Find the inner-most nested block source only; ignore any nested blocks.
				for ( i = validationError.sources.length - 1; 0 <= i; i-- ) {
					source = validationError.sources[ i ];

					if ( ! source.block_name || currentPost.id !== source.post_id ) {
						continue;
					}

					// @todo Cross-check source.block_name with wp.data.select( 'core/editor' ).getBlock( matchedBlockUid ).name?
					matchedBlockUid = blockOrder[ source.block_content_index ];
					if ( ! _.isUndefined( matchedBlockUid ) ) {
						blocksValidationErrors[ blockOrder[ source.block_content_index ] ].push( validationError );
						break;
					}
				}
			} );

			wp.data.dispatch( 'amp/blockValidation' ).updateBlocksValidationErrors( blocksValidationErrors );
		},

		/**
		 * Wraps the edit() method of a block, and conditionally adds a Notice.
		 *
		 * @param {Function} BlockEdit - The original edit() method of the block.
		 * @return {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
		 */
		conditionallyAddNotice: function( BlockEdit ) {
			var AmpNoticeBlockEdit = function( props ) {
				var edit = wp.element.createElement(
					BlockEdit,
					_.extend( {}, props, { key: 'amp-original-edit' } )
				);

				if ( 0 === props.ampBlockValidationErrors.length ) {
					return edit;
				}

				return [

					// @todo Add PanelBody with validation error details.
					wp.element.createElement(
						wp.components.Notice,
						{
							status: 'warning',
							isDismissible: false,
							key: 'amp-validation-notice'
						},
						module.data.i18n.invalidAmpContentNotice + ' ' + _.pluck( props.ampBlockValidationErrors, 'code' ).join( ', ' )
					),
					edit
				];
			};

			return wp.data.withSelect( function( select, ownProps ) {
				return _.extend( {}, ownProps, {
					ampBlockValidationErrors: select( 'amp/blockValidation' ).getBlockValidationErrors( ownProps.id )
				} );
			} )( AmpNoticeBlockEdit );
		}
	};

	return module;
}() );
