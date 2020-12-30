/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * External dependencies
 */
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from 'amp-block-validation';

export const BLOCK_VALIDATION_STORE_KEY = 'amp/block-validation';

const SET_IS_FETCHING_ERRORS = 'SET_IS_FETCHING_ERRORS';
const SET_IS_SHOWING_REVIEWED = 'SET_IS_SHOWING_REVIEWED';
const SET_REVIEW_LINK = 'SET_REVIEW_LINK';
const SET_VALIDATION_ERRORS = 'SET_VALIDATION_ERRORS';

export const INITIAL_STATE = {
	ampCompatibilityBroken: false,
	isFetchingErrors: false,
	isShowingReviewed: false,
	rawValidationErrors: [],
	reviewLink: null,
	reviewedValidationErrors: [],
	unreviewedValidationErrors: [],
	validationErrors: [],
};

export function getStore( initialState ) {
	return {
		reducer: ( state = initialState, action ) => {
			switch ( action.type ) {
				case SET_IS_FETCHING_ERRORS:
					return { ...state, isFetchingErrors: action.isFetchingErrors };

				case SET_IS_SHOWING_REVIEWED:
					return { ...state, isShowingReviewed: action.isShowingReviewed };

				case SET_REVIEW_LINK:
					return { ...state, reviewLink: action.reviewLink };

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
			setIsFetchingErrors: ( isFetchingErrors ) => ( { type: SET_IS_FETCHING_ERRORS, isFetchingErrors } ),
			setIsShowingReviewed: ( isShowingReviewed ) => ( { type: SET_IS_SHOWING_REVIEWED, isShowingReviewed } ),
			setReviewLink: ( reviewLink ) => ( { type: SET_REVIEW_LINK, reviewLink } ),
			setValidationErrors: ( validationErrors ) => ( { type: SET_VALIDATION_ERRORS, validationErrors } ),
		},
		selectors: {
			getAMPCompatibilityBroken: ( { ampCompatibilityBroken } ) => ampCompatibilityBroken,
			getIsFetchingErrors: ( { isFetchingErrors } ) => isFetchingErrors,
			getIsShowingReviewed: ( { isShowingReviewed } ) => isShowingReviewed,
			getReviewLink: ( { reviewLink } ) => reviewLink,
			getReviewedValidationErrors: ( { reviewedValidationErrors } ) => reviewedValidationErrors,
			getUnreviewedValidationErrors: ( { unreviewedValidationErrors } ) => unreviewedValidationErrors,
			getValidationErrors: ( { validationErrors } ) => validationErrors,
		},
		initialState,
	};
}

/**
 * Register the store for block validation.
 *
 * @param {Object} initialState Initial store state.
 */
export function createStore( initialState ) {
	registerStore( BLOCK_VALIDATION_STORE_KEY, getStore( initialState ) );
}
