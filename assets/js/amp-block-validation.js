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
			var lastValidationErrors, validationWarningNoticeId;
			module.data = data;

			wp.hooks.addFilter(
				'blocks.BlockEdit',
				'amp/add-notice',
				module.conditionallyAddNotice
			);

			module.store = wp.data.registerStore( 'amp/blockValidation', {
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

			wp.data.subscribe( function() {
				var currentPost, validationErrors, noticeAction, blockValidationErrors, noticeMessage;

				// @todo Gutenberg currently is not persisting isDirty state if changes are made during save request. Block order mismatch.
				// We can only align block validation errors with blocks in editor when in saved state.
				if ( wp.data.select( 'core/editor' ).isEditedPostDirty() ) {
					return;
				}

				currentPost = wp.data.select( 'core/editor' ).getCurrentPost();
				validationErrors = currentPost[ module.data.restValidationErrorsField ];
				if ( validationErrors && ! _.isEqual( lastValidationErrors, validationErrors ) ) {
					lastValidationErrors = validationErrors;

					if ( validationWarningNoticeId ) {
						wp.data.dispatch( 'core/editor' ).removeNotice( validationWarningNoticeId );
						validationWarningNoticeId = null;
					}

					if ( ! validationErrors.length ) {
						wp.data.dispatch( 'amp/blockValidation' ).updateBlocksValidationErrors( {} );
					} else {
						noticeMessage = wp.i18n.sprintf(
							wp.i18n._n(
								'There is %s AMP validation issues.',
								'There are %s AMP validation issues.',
								validationErrors.length
								// @todo Domain.
							),
							validationErrors.length
						);

						try {
							blockValidationErrors = module.getBlocksValidationErrors();
							wp.data.dispatch( 'amp/blockValidation' ).updateBlocksValidationErrors( blockValidationErrors.byUid );

							if ( validationErrors.length - blockValidationErrors.other.length > 0 ) {
								noticeMessage += ' ' + wp.i18n.sprintf(
									wp.i18n._n(
										'And %s of them is in your content blocks.',
										'And %s of them are in your content blocks.',
										validationErrors.length - blockValidationErrors.other.length
										// @todo Domain.
									),
									validationErrors.length - blockValidationErrors.other.length
								);
							} else {
								noticeMessage += ' ' + wp.i18n.__( 'But none of them are in your content.' ); // @todo Domain.
							}
						} catch ( e ) {
							wp.data.dispatch( 'amp/blockValidation' ).updateBlocksValidationErrors( {} ); // Empty it out.
						}

						noticeAction = wp.data.dispatch( 'core/editor' ).createWarningNotice(
							noticeMessage
						);
						validationWarningNoticeId = noticeAction.notice.id;
					}
				}
			} );
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
			validationErrors = currentPost[ module.data.restValidationErrorsField ];
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
		 * Wraps the edit() method of a block, and conditionally adds a Notice.
		 *
		 * @param {Function} BlockEdit - The original edit() method of the block.
		 * @return {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
		 */
		conditionallyAddNotice: function conditionallyAddNotice( BlockEdit ) {
			function AmpNoticeBlockEdit( props ) {
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
			}

			return wp.data.withSelect( function( select, ownProps ) {
				return _.extend( {}, ownProps, {
					ampBlockValidationErrors: select( 'amp/blockValidation' ).getBlockValidationErrors( ownProps.id )
				} );
			} )( AmpNoticeBlockEdit );
		}
	};

	return module;
}() );
