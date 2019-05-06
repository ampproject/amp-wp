/**
 * Validates blocks for AMP compatibility.
 *
 * This uses the REST API response from saving a page to find validation errors.
 * If one exists for a block, it display it inline with a Notice component.
 */

/**
 * External dependencies
 */
import { extend, each, map, filter, isEqual, isUndefined } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, _n, sprintf, setLocaleData } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { subscribe, registerStore, select, dispatch } from '@wordpress/data';
import { Notice } from '@wordpress/components';
import { createElement, Fragment } from '@wordpress/element';

const ampBlockValidation = ( function() { // eslint-disable-line no-unused-vars
	'use strict';

	const module = {

		/**
		 * Data exported from server.
		 *
		 * @param {Object}
		 */
		data: {
			i18n: {},
			ampValidityRestField: '',
			isSanitizationAutoAccepted: false,
			possibleStati: [],
			defaultStatus: '',
		},

		/**
		 * Name of the store.
		 *
		 * @param {string}
		 */
		storeName: 'amp/blockValidation',

		/**
		 * Holds the last states which are used for comparisons.
		 *
		 * @param {Object}
		 */
		lastStates: {
			noticesAreReset: false,
			validationErrors: [],
			blockOrder: [],
			blockValidationErrors: {},
		},

		/**
		 * Boot module.
		 *
		 * @param {Object} data - Module data.
		 * @return {void}
		 */
		boot( data ) {
			module.data = data;

			setLocaleData( module.data.i18n, 'amp' );

			addFilter(
				'editor.BlockEdit',
				'amp/add-notice',
				module.conditionallyAddNotice,
				99 // eslint-disable-line
			);

			module.store = module.registerStore();

			subscribe( module.handleValidationErrorsStateChange );
		},

		/**
		 * Register store.
		 *
		 * @return {Object} Store.
		 */
		registerStore() {
			return registerStore( module.storeName, {
				reducer( _state, action ) {
					const state = _state || {
						blockValidationErrorsByClientId: {},
					};

					switch ( action.type ) {
						case 'UPDATE_BLOCKS_VALIDATION_ERRORS':
							return extend( {}, state, {
								blockValidationErrorsByClientId: action.blockValidationErrorsByClientId,
							} );
						default:
							return state;
					}
				},
				actions: {
					updateBlocksValidationErrors( blockValidationErrorsByClientId ) {
						return {
							type: 'UPDATE_BLOCKS_VALIDATION_ERRORS',
							blockValidationErrorsByClientId,
						};
					},
				},
				selectors: {
					getBlockValidationErrors( state, clientId ) {
						return state.blockValidationErrorsByClientId[ clientId ] || [];
					},
				},
			} );
		},

		/**
		 * Checks if AMP is enabled for this post.
		 *
		 * @return {boolean} Returns true when the AMP toggle is on; else, false is returned.
		 */
		isAMPEnabled() {
			const meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
			if ( meta && meta.amp_status && module.data.possibleStati.includes( meta.amp_status ) ) {
				return 'enabled' === meta.amp_status;
			}
			return module.data.defaultStatus;
		},

		/**
		 * Checks if the validate errors state change handler should wait before processing.
		 *
		 * @return {boolean} Whether should wait.
		 */
		waitToHandleStateChange: function waitToHandleStateChange() {
			// @todo Gutenberg currently is not persisting isDirty state if changes are made during save request. Block order mismatch.
			// We can only align block validation errors with blocks in editor when in saved state, since only here will the blocks be aligned with the validation errors.
			if ( select( 'core/editor' ).isEditedPostDirty() || ( ! select( 'core/editor' ).isEditedPostDirty() && select( 'core/editor' ).isEditedPostNew() ) ) {
				return true;
			}

			// Wait for the current post to be set up.
			const currentPost = select( 'core/editor' ).getCurrentPost();

			return ! currentPost.hasOwnProperty( 'id' );
		},

		/**
		 * Handle state change regarding validation errors.
		 *
		 * This is essentially a JS implementation of \AMP_Validation_Manager::print_edit_form_validation_status() in PHP.
		 *
		 * @return {void}
		 */
		handleValidationErrorsStateChange: function handleValidationErrorsStateChange() {
			let blockValidationErrors, noticeMessage, blockErrorCount;

			if ( ! module.isAMPEnabled() ) {
				if ( ! module.lastStates.noticesAreReset ) {
					module.lastStates.validationErrors = [];
					module.lastStates.noticesAreReset = true;
					module.resetWarningNotice();
					module.resetBlockNotices();
				}
				return;
			}

			if ( module.waitToHandleStateChange() ) {
				return;
			}

			const currentPost = select( 'core/editor' ).getCurrentPost();
			const ampValidity = currentPost[ module.data.ampValidityRestField ] || {};

			// Show all validation errors which have not been explicitly acknowledged as accepted.
			const validationErrors = map(
				filter( ampValidity.results, function( result ) {
					// @todo Show VALIDATION_ERROR_ACK_REJECTED_STATUS differently since moderated?
					return (
						0 /* \AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS */ === result.status ||
						1 /* \AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS */ === result.status ||
						2 /* \AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS */ === result.status // eslint-disable-line no-magic-numbers
					);
				} ),
				function( result ) {
					return result.error;
				}
			);

			// Short-circuit if there was no change to the validation errors.
			if ( ! module.didValidationErrorsChange( validationErrors ) ) {
				if ( ! validationErrors.length && ! module.lastStates.noticesAreReset ) {
					module.lastStates.noticesAreReset = true;
					module.resetWarningNotice();
				}
				return;
			}
			module.lastStates.validationErrors = validationErrors;
			module.lastStates.noticesAreReset = false;

			// Remove any existing notice.
			module.resetWarningNotice();

			noticeMessage = sprintf(
				/* translators: %s: number of issues */
				_n(
					'There is %s issue from AMP validation which needs review.',
					'There are %s issues from AMP validation which need review.',
					validationErrors.length,
					'amp'
				),
				validationErrors.length
			);

			try {
				blockValidationErrors = module.getBlocksValidationErrors();
				module.lastStates.blockValidationErrors = blockValidationErrors.byClientId;
				dispatch( module.storeName ).updateBlocksValidationErrors( blockValidationErrors.byClientId );

				blockErrorCount = validationErrors.length - blockValidationErrors.other.length;
				if ( blockErrorCount > 0 ) {
					noticeMessage += ' ' + sprintf(
						/* translators: %s: number of block errors. */
						_n(
							'%s issue is directly due to content here.',
							'%s issues are directly due to content here.',
							blockErrorCount,
							'amp'
						),
						blockErrorCount
					);
				} else if ( validationErrors.length === 1 ) {
					noticeMessage += ' ' + __( 'The issue is not directly due to content here.', 'amp' );
				} else {
					noticeMessage += ' ' + __( 'The issues are not directly due to content here.', 'amp' );
				}
			} catch ( e ) {
				// Clear out block validation errors in case the block sand errors cannot be aligned.
				module.resetBlockNotices();

				if ( validationErrors.length === 1 ) {
					noticeMessage += ' ' + __( 'The issue may not be due to content here', 'amp' );
				} else {
					noticeMessage += ' ' + __( 'Some issues may be due to content here.', 'amp' );
				}
			}

			const rejectedErrors = filter( ampValidity.results, function( result ) {
				return (
					0 /* \AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS */ === result.status ||
					2 /* \AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS */ === result.status // eslint-disable-line no-magic-numbers
				);
			} );

			noticeMessage += ' ';
			// Auto-acceptance is from either checking 'Automatically accept sanitization...' or from being in Native mode.
			if ( module.data.isSanitizationAutoAccepted ) {
				if ( 0 === rejectedErrors.length ) {
					noticeMessage += __( 'However, your site is configured to automatically accept sanitization of the offending markup.', 'amp' );
				} else {
					noticeMessage += _n(
						'Your site is configured to automatically accept sanitization errors, but this error could be from when auto-acceptance was not selected, or from manually rejecting an error.',
						'Your site is configured to automatically accept sanitization errors, but these errors could be from when auto-acceptance was not selected, or from manually rejecting an error.',
						validationErrors.length,
						'amp'
					);
				}
			} else {
				noticeMessage += __( 'Non-accepted validation errors prevent AMP from being served, and the user will be redirected to the non-AMP version.', 'amp' );
			}

			const noticeOptions = {
				id: 'amp-errors-notice',
			};
			if ( ampValidity.review_link ) {
				noticeOptions.actions = [
					{
						label: __( 'Review issues', 'amp' ),
						url: ampValidity.review_link,
					},
				];
			}

			// Display notice if there were validation errors.
			if ( validationErrors.length > 0 ) {
				dispatch( 'core/notices' ).createNotice( 'warning', noticeMessage, noticeOptions );
			}

			module.validationWarningNoticeId = noticeOptions.id;
		},

		/**
		 * Checks if the validation errors have changed.
		 *
		 * @param {Object[]} validationErrors A list of validation errors.
		 * @return {boolean|*} Returns true when the validation errors change.
		 */
		didValidationErrorsChange: function didValidationErrorsChange( validationErrors ) {
			if ( module.areBlocksOutOfSync() ) {
				module.lastStates.validationErrors = [];
			}

			return (
				module.lastStates.validationErrors.length !== validationErrors.length ||
				( validationErrors && ! isEqual( module.lastStates.validationErrors, validationErrors ) )
			);
		},

		/**
		 * Checks if the block order is out of sync.
		 *
		 * Block change on page load and can get out of sync during normal editing and saving processes.  This method gives a check to determine if an "out of sync" condition occurred.
		 *
		 * @return {boolean} Whether out of sync.
		 */
		areBlocksOutOfSync: function areBlocksOutOfSync() {
			const blockOrder = select( 'core/editor' ).getBlockOrder();
			if ( module.lastStates.blockOrder.length !== blockOrder.length || ! isEqual( module.lastStates.blockOrder, blockOrder ) ) {
				module.lastStates.blockOrder = blockOrder;
				return true;
			}

			return false;
		},

		/**
		 * Resets the validation warning notice.
		 *
		 * @return {void}
		 */
		resetWarningNotice: function resetWarningNotice() {
			if ( module.validationWarningNoticeId ) {
				dispatch( 'core/notices' ).removeNotice( module.validationWarningNoticeId );
				module.validationWarningNoticeId = null;
			}
		},

		/**
		 * Resets the block level validation errors.
		 *
		 * @return {void}
		 */
		resetBlockNotices: function resetBlockNotices() {
			dispatch( module.storeName ).updateBlocksValidationErrors( {} );
		},

		/**
		 * Get flattened block order.
		 *
		 * @param {Object[]} blocks - List of blocks which maty have nested blocks inside them.
		 * @return {string[]} Block IDs in flattened order.
		 */
		getFlattenedBlockOrder: function getFlattenedBlockOrder( blocks ) {
			const blockOrder = [];
			each( blocks, function( block ) {
				blockOrder.push( block.clientId );
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
			const acceptedStatus = 3; // eslint-disable-line no-magic-numbers
			const editorSelect = select( 'core/editor' );
			const currentPost = editorSelect.getCurrentPost();
			const validationErrors = map(
				filter( currentPost[ module.data.ampValidityRestField ].results, function( result ) {
					return result.term_status !== acceptedStatus; // If not accepted by the user.
				} ),
				function( result ) {
					return result.error;
				}
			);
			const blockOrder = module.getFlattenedBlockOrder( editorSelect.getBlocks() );
			const otherValidationErrors = [];
			const blockValidationErrorsByClientId = {};

			each( blockOrder, function( clientId ) {
				blockValidationErrorsByClientId[ clientId ] = [];
			} );

			each( validationErrors, function( validationError ) {
				let i, source, clientId, block, matched;
				if ( ! validationError.sources ) {
					otherValidationErrors.push( validationError );
					return;
				}

				// Find the inner-most nested block source only; ignore any nested blocks.
				matched = false;
				for ( i = validationError.sources.length - 1; 0 <= i; i-- ) {
					source = validationError.sources[ i ];

					// Skip sources that are not for blocks.
					if ( ! source.block_name || isUndefined( source.block_content_index ) || currentPost.id !== source.post_id ) {
						continue;
					}

					// Look up the block ID by index, assuming the blocks of content in the editor are the same as blocks rendered on frontend.
					clientId = blockOrder[ source.block_content_index ];
					if ( isUndefined( clientId ) ) {
						throw new Error( 'undefined_block_index' );
					}

					// Sanity check that block exists for clientId.
					block = editorSelect.getBlock( clientId );
					if ( ! block ) {
						throw new Error( 'block_lookup_failure' );
					}

					// Check the block type in case a block is dynamically added/removed via the_content filter to cause alignment error.
					if ( block.name !== source.block_name ) {
						throw new Error( 'ordered_block_alignment_mismatch' );
					}

					blockValidationErrorsByClientId[ clientId ].push( validationError );
					matched = true;

					// Stop looking for sources, since we aren't looking for parent blocks.
					break;
				}

				if ( ! matched ) {
					otherValidationErrors.push( validationError );
				}
			} );

			return {
				byClientId: blockValidationErrorsByClientId,
				other: otherValidationErrors,
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
					__( 'Invalid element: ', 'amp' ),
					createElement( 'code', { key: 'name' }, validationError.node_name ),
				];
			} else if ( 'invalid_attribute' === validationError.code && validationError.node_name ) {
				return [
					__( 'Invalid attribute: ', 'amp' ),
					createElement( 'code', { key: 'name' }, validationError.parent_name ? sprintf( '%s[%s]', validationError.parent_name, validationError.node_name ) : validationError.node_name ),
				];
			}
			return [
				__( 'Error code: ', 'amp' ),
				createElement( 'code', { key: 'name' }, validationError.code || __( 'unknown', 'amp' ) ),
			];
		},

		/**
		 * Wraps the edit() method of a block, and conditionally adds a Notice.
		 *
		 * @param {Function} BlockEdit - The original edit() method of the block.
		 * @return {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
		 */
		conditionallyAddNotice: function conditionallyAddNotice( BlockEdit ) {
			return function( ownProps ) {
				let validationErrors;

				function AmpNoticeBlockEdit( props ) {
					const edit = createElement(
						BlockEdit,
						props
					);

					if ( 0 === props.ampBlockValidationErrors.length ) {
						return edit;
					}

					const details = createElement( 'details', { className: 'amp-block-validation-errors' }, [
						createElement( 'summary', { key: 'summary', className: 'amp-block-validation-errors__summary' }, sprintf(
							_n(
								'There is %s issue from AMP validation.',
								'There are %s issues from AMP validation.',
								props.ampBlockValidationErrors.length,
								'amp'
							),
							props.ampBlockValidationErrors.length
						) ),
						createElement(
							'ul',
							{ key: 'list', className: 'amp-block-validation-errors__list' },
							map( props.ampBlockValidationErrors, function( error, key ) {
								return createElement( 'li', { key }, module.getValidationErrorMessage( error ) );
							} )
						),
					] );

					return createElement(
						Fragment, {},
						createElement(
							Notice,
							{
								status: 'warning',
								isDismissible: false,
							},
							details
						),
						edit
					);
				}

				if ( ! module.lastStates.blockValidationErrors[ ownProps.clientId ] ) {
					validationErrors = select( module.storeName ).getBlockValidationErrors( ownProps.clientId );
					module.lastStates.blockValidationErrors[ ownProps.clientId ] = validationErrors;
				}

				const mergedProps = extend( {}, ownProps, {
					ampBlockValidationErrors: module.lastStates.blockValidationErrors[ ownProps.clientId ],
				} );

				return AmpNoticeBlockEdit( mergedProps );
			};
		},
	};

	return module;
}() );

window.ampBlockValidation = ampBlockValidation;
