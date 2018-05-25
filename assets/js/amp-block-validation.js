/**
 * Validates blocks for AMP compatibility.
 *
 * This uses the REST API response from saving a page to find validation errors.
 * If one exists for a block, it display it inline with a Notice component.
 */

/* exported ampBlockValidation */
/* global wp, _ */
var ampBlockValidation = ( function() { // eslint-disable-line no-unused-vars
	'use strict';

	var module = {

		/**
		 * Data exported from server.
		 *
		 * @param {Object}
		 */
		data: {
			i18n: {},
			ampValidityRestField: ''
		},

		/**
		 * Name of the store.
		 *
		 * @param {string}
		 */
		storeName: 'amp/blockValidation',

		/**
		 * Boot module.
		 *
		 * @param {Object} data - Module data.
		 * @return {void}
		 */
		boot: function boot( data ) {
			module.data = data;

			wp.i18n.setLocaleData( module.data.i18n, 'amp' );

			wp.hooks.addFilter(
				'blocks.BlockEdit',
				'amp/add-notice',
				module.conditionallyAddNotice
			);

			module.store = module.registerStore();

			wp.data.subscribe( module.handleValidationErrorsStateChange );
		},

		/**
		 * Register store.
		 *
		 * @return {Object} Store.
		 */
		registerStore: function registerStore() {
			return wp.data.registerStore( module.storeName, {
				reducer: function( _state, action ) {
					var state = _state || {
						blockValidationErrorsByUid: {}
					};

					switch ( action.type ) {
						case 'UPDATE_BLOCKS_VALIDATION_ERRORS':
							return _.extend( {}, state, {
								blockValidationErrorsByUid: action.blockValidationErrorsByUid
							} );
						default:
							return state;
					}
				},
				actions: {
					updateBlocksValidationErrors: function( blockValidationErrorsByUid ) {
						return {
							type: 'UPDATE_BLOCKS_VALIDATION_ERRORS',
							blockValidationErrorsByUid: blockValidationErrorsByUid
						};
					}
				},
				selectors: {
					getBlockValidationErrors: function( state, uid ) {
						return state.blockValidationErrorsByUid[ uid ] || [];
					}
				}
			} );
		},

		/**
		 * Handle state change regarding validation errors.
		 *
		 * @return {void}
		 */
		handleValidationErrorsStateChange: function handleValidationErrorsStateChange() {
			var currentPost, validationErrors, blockValidationErrors, noticeElement, noticeMessage, blockErrorCount, ampValidity;

			// @todo Gutenberg currently is not persisting isDirty state if changes are made during save request. Block order mismatch.
			// We can only align block validation errors with blocks in editor when in saved state, since only here will the blocks be aligned with the validation errors.
			if ( wp.data.select( 'core/editor' ).isEditedPostDirty() ) {
				return;
			}

			currentPost = wp.data.select( 'core/editor' ).getCurrentPost();
			ampValidity = currentPost[ module.data.ampValidityRestField ] || {};
			validationErrors = ampValidity.errors;

			// Short-circuit if there was no change to the validation errors.
			if ( ! validationErrors || _.isEqual( module.lastValidationErrors, validationErrors ) ) {
				return;
			}
			module.lastValidationErrors = validationErrors;

			// Remove any existing notice.
			if ( module.validationWarningNoticeId ) {
				wp.data.dispatch( 'core/editor' ).removeNotice( module.validationWarningNoticeId );
				module.validationWarningNoticeId = null;
			}

			// If there are no validation errors then just make sure the validation notices are cleared from the blocks.
			if ( ! validationErrors.length ) {
				wp.data.dispatch( module.storeName ).updateBlocksValidationErrors( {} );
				return;
			}

			noticeMessage = wp.i18n.sprintf(
				wp.i18n._n(
					'There is %s issue from AMP validation.',
					'There are %s issues from AMP validation.',
					validationErrors.length,
					'amp'
				),
				validationErrors.length
			);

			try {
				blockValidationErrors = module.getBlocksValidationErrors();
				wp.data.dispatch( module.storeName ).updateBlocksValidationErrors( blockValidationErrors.byUid );

				blockErrorCount = validationErrors.length - blockValidationErrors.other.length;
				if ( blockErrorCount > 0 ) {
					noticeMessage += ' ' + wp.i18n.sprintf(
						wp.i18n._n(
							'And %s is directly due to content here.',
							'And %s are directly due to content here.',
							blockErrorCount,
							'amp'
						),
						blockErrorCount
					);
				} else {
					noticeMessage += ' ' + wp.i18n.sprintf(
						wp.i18n._n(
							'But it is not directly due to content here.',
							'But none are directly due to content here.',
							validationErrors.length,
							'amp'
						),
						validationErrors.length
					);
				}
			} catch ( e ) {
				// Clear out block validation errors in case the block sand errors cannot be aligned.
				wp.data.dispatch( module.storeName ).updateBlocksValidationErrors( {} );

				noticeMessage += ' ' + wp.i18n._n(
					'It may not be due to content here.',
					'Some may be due to content here.',
					validationErrors.length,
					'amp'
				);
			}

			noticeMessage += ' ' + wp.i18n.__( 'Invalid code is stripped when displaying AMP.', 'amp' );
			noticeElement = wp.element.createElement( 'p', {}, [
				noticeMessage + ' ',
				ampValidity.link && wp.element.createElement(
					'a',
					{ key: 'details', href: ampValidity.link, target: '_blank' },
					wp.i18n.__( 'Details', 'amp' )
				)
			] );

			module.validationWarningNoticeId = wp.data.dispatch( 'core/editor' ).createWarningNotice( noticeElement, { spokenMessage: noticeMessage } ).notice.id;
		},

		/**
		 * Get flattened block order.
		 *
		 * @param {Object[]} blocks - List of blocks which maty have nested blocks inside them.
		 * @return {string[]} Block IDs in flattened order.
		 */
		getFlattenedBlockOrder: function getFlattenedBlockOrder( blocks ) {
			var blockOrder = [];
			_.each( blocks, function( block ) {
				blockOrder.push( block.uid );
				if ( block.innerBlocks.length > 0 ) {
					Array.prototype.push.apply( blockOrder, module.getFlattenedBlockOrder( block.innerBlocks ) );
				}
			} );
			return blockOrder;
		},

		/**
		 * Update blocks' validation errors in the store.
		 *
		 * @return {Object} Validation errors grouped by block ID other ones.
		 */
		getBlocksValidationErrors: function getBlocksValidationErrors() {
			var blockValidationErrorsByUid, editorSelect, currentPost, blockOrder, validationErrors, otherValidationErrors;
			editorSelect = wp.data.select( 'core/editor' );
			currentPost = editorSelect.getCurrentPost();
			validationErrors = currentPost[ module.data.ampValidityRestField ].errors;
			blockOrder = module.getFlattenedBlockOrder( editorSelect.getBlocks() );

			otherValidationErrors = [];
			blockValidationErrorsByUid = {};
			_.each( blockOrder, function( uid ) {
				blockValidationErrorsByUid[ uid ] = [];
			} );

			_.each( validationErrors, function( validationError ) {
				var i, source, uid, block, matched;
				if ( ! validationError.sources ) {
					otherValidationErrors.push( validationError );
					return;
				}

				// Find the inner-most nested block source only; ignore any nested blocks.
				matched = false;
				for ( i = validationError.sources.length - 1; 0 <= i; i-- ) {
					source = validationError.sources[ i ];

					// Skip sources that are not for blocks.
					if ( ! source.block_name || _.isUndefined( source.block_content_index ) || currentPost.id !== source.post_id ) {
						continue;
					}

					// Look up the block ID by index, assuming the blocks of content in the editor are the same as blocks rendered on frontend.
					uid = blockOrder[ source.block_content_index ];
					if ( _.isUndefined( uid ) ) {
						throw new Error( 'undefined_block_index' );
					}

					// Sanity check that block exists for uid.
					block = editorSelect.getBlock( uid );
					if ( ! block ) {
						throw new Error( 'block_lookup_failure' );
					}

					// Check the block type in case a block is dynamically added/removed via the_content filter to cause alignment error.
					if ( block.name !== source.block_name ) {
						throw new Error( 'ordered_block_alignment_mismatch' );
					}

					blockValidationErrorsByUid[ uid ].push( validationError );
					matched = true;

					// Stop looking for sources, since we aren't looking for parent blocks.
					break;
				}

				if ( ! matched ) {
					otherValidationErrors.push( validationError );
				}
			} );

			return {
				byUid: blockValidationErrorsByUid,
				other: otherValidationErrors
			};
		},

		/**
		 * Get message for validation error.
		 *
		 * @param {Object} validationError - Validation error.
		 * @param {string} validationError.code - Validation error code.
		 * @param {string} [validationError.node_name] - Node name.
		 * @param {string} [validationError.message] - Validation error message.
		 * @return {wp.element.Component[]|string[]} Validation error message.
		 */
		getValidationErrorMessage: function getValidationErrorMessage( validationError ) {
			if ( validationError.message ) {
				return validationError.message;
			}
			if ( 'invalid_element' === validationError.code && validationError.node_name ) {
				return [
					wp.i18n.__( 'Invalid element: ' ),
					wp.element.createElement( 'code', { key: 'name' }, validationError.node_name )
				];
			} else if ( 'invalid_attribute' === validationError.code && validationError.node_name ) {
				return [
					wp.i18n.__( 'Invalid attribute: ' ),
					wp.element.createElement( 'code', { key: 'name' }, validationError.parent_name ? wp.i18n.sprintf( '%s[%s]', validationError.parent_name, validationError.node_name ) : validationError.node_name )
				];
			}
			return [
				wp.i18n.__( 'Error code: ', 'amp' ),
				wp.element.createElement( 'code', { key: 'name' }, validationError.code || wp.i18n.__( 'unknown' ) )
			];
		},

		/**
		 * Wraps the edit() method of a block, and conditionally adds a Notice.
		 *
		 * @param {Function} BlockEdit - The original edit() method of the block.
		 * @return {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
		 */
		conditionallyAddNotice: function conditionallyAddNotice( BlockEdit ) {
			function AmpNoticeBlockEdit( props ) {
				var edit, details;
				edit = wp.element.createElement(
					BlockEdit,
					props
				);

				if ( 0 === props.ampBlockValidationErrors.length ) {
					return edit;
				}

				details = wp.element.createElement( 'details', { className: 'amp-block-validation-errors' }, [
					wp.element.createElement( 'summary', { key: 'summary', className: 'amp-block-validation-errors__summary' }, wp.i18n.sprintf(
						wp.i18n._n(
							'There is %s issue from AMP validation.',
							'There are %s issues from AMP validation.',
							props.ampBlockValidationErrors.length,
							'amp'
						),
						props.ampBlockValidationErrors.length
					) ),
					wp.element.createElement(
						'ul',
						{ key: 'list', className: 'amp-block-validation-errors__list' },
						_.map( props.ampBlockValidationErrors, function( error, key ) {
							return wp.element.createElement( 'li', { key: key }, module.getValidationErrorMessage( error ) );
						} )
					)
				] );

				return wp.element.createElement(
					wp.element.Fragment, {},
					wp.element.createElement(
						wp.components.Notice,
						{
							status: 'warning',
							isDismissible: false
						},
						details
					),
					edit
				);
			}

			return wp.data.withSelect( function( select, ownProps ) {
				return _.extend( {}, ownProps, {
					ampBlockValidationErrors: select( module.storeName ).getBlockValidationErrors( ownProps.id )
				} );
			} )( AmpNoticeBlockEdit );
		}
	};

	return module;
}() );
