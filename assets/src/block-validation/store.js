/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from './constants';

export const BLOCK_VALIDATION_STORE_KEY = 'amp/block-validation';

const SET_IS_SHOWING_REVIEWED = 'SET_IS_SHOWING_REVIEWED';
const SET_VALIDATION_ERRORS = 'SET_VALIDATION_ERRORS';

export const INITIAL_STATE = {
	ampCompatibilityBroken: false,
	isShowingReviewed: false,
	rawValidationErrors: [],
	reviewLink: null,
	reviewedValidationErrors: [],
	unreviewedValidationErrors: [],
	validationErrors: [],
};

/**
 * Register the store for block validation.
 *
 * @param {Object} initialState Initial store state.
 */
export function createStore( initialState ) {
	registerStore(
		BLOCK_VALIDATION_STORE_KEY,
		{
			reducer: ( state = initialState, action ) => {
				switch ( action.type ) {
					case SET_IS_SHOWING_REVIEWED:
						return { ...state, isShowingReviewed: action.isShowingReviewed };

					case SET_VALIDATION_ERRORS:
						return {
							...state,
							ampCompatibilityBroken: Boolean(
								action.validationErrors.filter( ( { status } ) =>
									status === VALIDATION_ERROR_NEW_REJECTED_STATUS || status === VALIDATION_ERROR_ACK_REJECTED_STATUS,
								)?.length,
							),

							reviewedValidationErrors: action.validationErrors
								.filter( ( { status } ) =>
									status === VALIDATION_ERROR_ACK_ACCEPTED_STATUS || status === VALIDATION_ERROR_ACK_REJECTED_STATUS,
								),

							unreviewedValidationErrors: action.validationErrors
								.filter( ( { status } ) =>
									status === VALIDATION_ERROR_NEW_ACCEPTED_STATUS || status === VALIDATION_ERROR_NEW_REJECTED_STATUS,
								),

							validationErrors: action.validationErrors,
						};

					default:
						return state;
				}
			},
			actions: {
				setIsShowingReviewed: ( isShowingReviewed ) => ( { type: SET_IS_SHOWING_REVIEWED, isShowingReviewed } ),
				setValidationErrors: ( validationErrors ) => ( { type: SET_VALIDATION_ERRORS, validationErrors } ),
			},
			selectors: {
				getAMPCompatibilityBroken: ( { ampCompatibilityBroken } ) => ampCompatibilityBroken,
				getIsShowingReviewed: ( { isShowingReviewed } ) => isShowingReviewed,
				getValidationErrors: ( { validationErrors } ) => validationErrors,
				getReviewedValidationErrors: ( { reviewedValidationErrors } ) => reviewedValidationErrors,
				getUnreviewedValidationErrors: ( { unreviewedValidationErrors } ) => unreviewedValidationErrors,
			},
			initialState,
		},
	);
}
